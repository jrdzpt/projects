<?php
session_set_cookie_params([
    'path' => '/',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/json; charset=utf-8');

// ── Auth guard ───────────────────────────────────────────────────────
if (empty($_SESSION['css_user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized. Please log in.', 'redirect' => 'login.php']);
    exit;
}

$action   = $_REQUEST['action'] ?? '';
$db       = getDB();
$_uid     = (int)($_SESSION['css_user_id'] ?? 0);
$_uname   = $db->real_escape_string($_SESSION['css_user'] ?? 'unknown');
$_ip      = $db->real_escape_string($_SERVER['REMOTE_ADDR'] ?? '');

// ── Audit helper ─────────────────────────────────────────────────────
function auditLog($db, $uid, $uname, $action, $recordId, $details, $ip) {
    $details = $db->real_escape_string($details ?? '');
    $action  = $db->real_escape_string($action);
    $rid     = $recordId ? (int)$recordId : 'NULL';
    $db->query("INSERT INTO cs_audit_log (user_id, username, action, record_id, details, ip_address)
                VALUES ($uid, '$uname', '$action', $rid, '$details', '$ip')");
}

function str(string $key, array $src = []): string {
    $src = $src ?: $_POST;
    return trim($src[$key] ?? '');
}


switch ($action) {


    case 'list':
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $limit   = RECORDS_PER_PAGE;
        $offset  = ($page - 1) * $limit;
        $search  = str('search', $_GET);
        $concern = str('concern', $_GET);
        $area    = str('area', $_GET);
        $date_from = str('date_from', $_GET);
        $date_to   = str('date_to', $_GET);

        $where = ['1=1'];
        if ($search) {
            $s = $db->real_escape_string($search);
            $where[] = "(reference_no LIKE '%$s%' OR account_number LIKE '%$s%' OR account_name LIKE '%$s%' OR contact_no LIKE '%$s%' OR messenger_caller LIKE '%$s%')";
        }
        if ($concern) {
            if ($concern === '__OTHER__') {
                // Show all records whose concern is NOT in the standard dropdown list
                $standardConcerns = getDropdownOptions('concern');
                if (!empty($standardConcerns)) {
                    $escaped = array_map(fn($v) => "'" . $db->real_escape_string($v) . "'", $standardConcerns);
                    $where[] = "concern NOT IN (" . implode(',', $escaped) . ") AND concern != ''";
                }
            } else {
                $v = $db->real_escape_string($concern);
                $where[] = "concern='$v'";
            }
        }
        if ($area) {
            if ($area === '__OTHER__') {
                // Show all records whose area_dept is NOT in the standard dropdown list
                $standardAreas = getDropdownOptions('area_dept');
                if (!empty($standardAreas)) {
                    $escaped = array_map(fn($v) => "'" . $db->real_escape_string($v) . "'", $standardAreas);
                    $where[] = "area_dept NOT IN (" . implode(',', $escaped) . ") AND area_dept != ''";
                }
            } else {
                $v = $db->real_escape_string($area);
                $where[] = "area_dept='$v'";
            }
        }
        if ($date_from){ $v = $db->real_escape_string($date_from); $where[] = "date_forwarded>='$v'"; }
        if ($date_to)  { $v = $db->real_escape_string($date_to);   $where[] = "date_forwarded<='$v'"; }

        $allowedCols = ['reference_no','account_number','account_name','concern','area_dept','date_forwarded','created_at'];
        $sortCol = in_array(str('sort_col',$_GET), $allowedCols) ? str('sort_col',$_GET) : 'created_at';
        $sortDir = strtolower(str('sort_dir',$_GET))==='asc' ? 'ASC' : 'DESC';

        $wSql  = implode(' AND ', $where);
        $total = $db->query("SELECT COUNT(*) AS c FROM cs_records WHERE $wSql")->fetch_assoc()['c'];
        $rows  = [];
        $res   = $db->query("SELECT * FROM cs_records WHERE $wSql ORDER BY $sortCol $sortDir LIMIT $limit OFFSET $offset");
        while ($r = $res->fetch_assoc()) { $rows[] = $r; }

        echo json_encode(['success'=>true,'data'=>$rows,'total'=>(int)$total,'page'=>$page,'pages'=>(int)ceil($total/$limit)]);
        break;


    case 'get':
        $id  = (int)($_GET['id'] ?? 0);
        $row = $db->query("SELECT * FROM cs_records WHERE id=$id")->fetch_assoc();
        echo json_encode($row ? ['success'=>true,'data'=>$row] : ['success'=>false,'message'=>'Not found']);
        break;


    case 'create':
        $fields = ['reference_no','account_number','account_name','address','landmark','contact_no',
                   'messenger_caller','concern','area_dept','date_forwarded','notes'];
        $data = [];
        foreach ($fields as $f) $data[$f] = $db->real_escape_string(str($f));
        $data['status'] = '';

        if (empty($data['reference_no'])) {
            echo json_encode(['success'=>false,'message'=>'Reference No is required.']);
            break;
        }

        $sql = "INSERT INTO cs_records (reference_no,account_number,account_name,address,landmark,
                    contact_no,messenger_caller,concern,area_dept,date_forwarded,notes,status)
                VALUES ('{$data['reference_no']}','{$data['account_number']}','{$data['account_name']}',
                        '{$data['address']}','{$data['landmark']}','{$data['contact_no']}',
                        '{$data['messenger_caller']}','{$data['concern']}','{$data['area_dept']}',
                        '{$data['date_forwarded']}','{$data['notes']}','{$data['status']}')";
        if ($db->query($sql)) {
            $newId = $db->insert_id;
            auditLog($db, $_uid, $_uname, 'create', $newId, "Created record: {$data['reference_no']}", $_ip);
            echo json_encode(['success'=>true,'id'=>$newId,'reference_no'=>$data['reference_no'],'message'=>'Record created successfully.']);
        } else {
 
            if ($db->errno === 1062) {
                echo json_encode(['success'=>false,'message'=>'Reference No already exists. Please use a unique reference number.']);
            } else {
                echo json_encode(['success'=>false,'message'=>$db->error]);
            }
        }
        break;


    case 'update':
        $id = (int)($_POST['id'] ?? 0);
        $fields = ['reference_no','account_number','account_name','address','landmark','contact_no',
                   'messenger_caller','concern','area_dept','date_forwarded','notes'];
        $sets = [];
        foreach ($fields as $f) {
            $v = $db->real_escape_string(str($f));
            $sets[] = "$f='$v'";
        }
        $sql = "UPDATE cs_records SET " . implode(',', $sets) . " WHERE id=$id";
        if ($db->query($sql)) {
            auditLog($db, $_uid, $_uname, 'update', $id, "Updated record ID $id", $_ip);
            echo json_encode(['success'=>true,'message'=>'Record updated successfully.']);
        } else {
            echo json_encode(['success'=>false,'message'=>$db->error]);
        }
        break;


    case 'delete':
        $id  = (int)($_POST['id'] ?? 0);
        $by  = $db->real_escape_string(str('archived_by') ?: 'admin');
        $row = $db->query("SELECT * FROM cs_records WHERE id=$id")->fetch_assoc();
        if (!$row) { echo json_encode(['success'=>false,'message'=>'Record not found']); break; }


        $db->query("INSERT INTO cs_records_archive
            (original_id,reference_no,account_number,account_name,address,landmark,
             contact_no,messenger_caller,concern,area_dept,date_forwarded,notes,status,created_at,archived_by)
            VALUES
            ({$row['id']},
             '{$db->real_escape_string($row['reference_no'])}',
             '{$db->real_escape_string($row['account_number'])}',
             '{$db->real_escape_string($row['account_name'])}',
             '{$db->real_escape_string($row['address'])}',
             '{$db->real_escape_string($row['landmark'])}',
             '{$db->real_escape_string($row['contact_no'])}',
             '{$db->real_escape_string($row['messenger_caller'])}',
             '{$db->real_escape_string($row['concern'])}',
             '{$db->real_escape_string($row['area_dept'])}',
             '{$row['date_forwarded']}',
             '{$db->real_escape_string($row['notes'])}',
             '{$row['status']}',
             '{$row['created_at']}',
             '$by')");

        $db->query("DELETE FROM cs_records WHERE id=$id");
        auditLog($db, $_uid, $_uname, 'archive', $id, "Archived record: {$row['reference_no']}", $_ip);
        echo json_encode(['success'=>true,'message'=>'Record archived successfully.']);
        break;


    case 'restore':
        $id  = (int)($_POST['id'] ?? 0);
        $row = $db->query("SELECT * FROM cs_records_archive WHERE id=$id")->fetch_assoc();
        if (!$row) { echo json_encode(['success'=>false,'message'=>'Archive record not found']); break; }

        $db->query("INSERT INTO cs_records
            (reference_no,account_number,account_name,address,landmark,
             contact_no,messenger_caller,concern,area_dept,date_forwarded,notes,status,created_at)
            VALUES
            ('{$db->real_escape_string($row['reference_no'])}',
             '{$db->real_escape_string($row['account_number'])}',
             '{$db->real_escape_string($row['account_name'])}',
             '{$db->real_escape_string($row['address'])}',
             '{$db->real_escape_string($row['landmark'])}',
             '{$db->real_escape_string($row['contact_no'])}',
             '{$db->real_escape_string($row['messenger_caller'])}',
             '{$db->real_escape_string($row['concern'])}',
             '{$db->real_escape_string($row['area_dept'])}',
             '{$row['date_forwarded']}',
             '{$db->real_escape_string($row['notes'])}',
             '{$db->real_escape_string($row['status'])}',
             '{$row['created_at']}')");

        $db->query("DELETE FROM cs_records_archive WHERE id=$id");
        auditLog($db, $_uid, $_uname, 'restore', $db->insert_id, "Restored record: {$row['reference_no']}", $_ip);
        echo json_encode(['success'=>true,'message'=>'Record restored successfully.']);
        break;


    case 'list_archive':
        $rows = [];
        $res  = $db->query("SELECT * FROM cs_records_archive ORDER BY archived_at DESC");
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success'=>true,'data'=>$rows]);
        break;

  
    case 'dropdowns':
        // Get standard dropdown options from config table
        $concernOptions = getDropdownOptions('concern');
        $areaOptions    = getDropdownOptions('area_dept');

        // Always append __OTHER__ so filter dropdowns always show "Other (Specify)"
        // This lets users filter by any manually-typed value saved in records
        $concernOptions[] = '__OTHER__';
        $areaOptions[]    = '__OTHER__';

        echo json_encode([
            'success'   => true,
            'concern'   => $concernOptions,
            'area_dept' => $areaOptions,
        ]);
        break;

  
    case 'stats':
        $total    = $db->query("SELECT COUNT(*) AS c FROM cs_records")->fetch_assoc()['c'];
        $archived = $db->query("SELECT COUNT(*) AS c FROM cs_records_archive")->fetch_assoc()['c'];
        $today    = $db->query("SELECT COUNT(*) AS c FROM cs_records WHERE DATE(created_at)=CURDATE()")->fetch_assoc()['c'];
        echo json_encode(compact('total','archived','today') + ['success'=>true]);
        break;

    case 'daily_summary':
        $date = $db->real_escape_string(str('date', $_GET) ?: date('Y-m-d'));
        $byConcern = [];
        $res = $db->query("SELECT concern, COUNT(*) AS cnt FROM cs_records WHERE date_forwarded='$date' GROUP BY concern ORDER BY cnt DESC");
        while ($r = $res->fetch_assoc()) $byConcern[] = $r;
        $byArea = [];
        $res = $db->query("SELECT area_dept, COUNT(*) AS cnt FROM cs_records WHERE date_forwarded='$date' GROUP BY area_dept ORDER BY cnt DESC");
        while ($r = $res->fetch_assoc()) $byArea[] = $r;
        $total = $db->query("SELECT COUNT(*) AS c FROM cs_records WHERE date_forwarded='$date'")->fetch_assoc()['c'];
        echo json_encode(['success'=>true,'date'=>$date,'total'=>(int)$total,'by_concern'=>$byConcern,'by_area'=>$byArea]);
        break;

    case 'dropdown_counts':
        $counts = ['concern'=>[], 'area_dept'=>[]];
        $res = $db->query("SELECT concern, COUNT(*) AS cnt FROM cs_records GROUP BY concern");
        while ($r = $res->fetch_assoc()) $counts['concern'][$r['concern']] = (int)$r['cnt'];
        $res = $db->query("SELECT area_dept, COUNT(*) AS cnt FROM cs_records GROUP BY area_dept");
        while ($r = $res->fetch_assoc()) $counts['area_dept'][$r['area_dept']] = (int)$r['cnt'];
        echo json_encode(['success'=>true,'counts'=>$counts]);
        break;

    case 'audit_log':
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 50;
        $offset = ($page - 1) * $limit;
        $uFilter = $db->real_escape_string(str('filter_user', $_GET));
        $aFilter = $db->real_escape_string(str('filter_action', $_GET));
        $where   = ['1=1'];
        if ($uFilter) $where[] = "username='$uFilter'";
        if ($aFilter) $where[] = "action='$aFilter'";
        $wSql  = implode(' AND ', $where);
        $total = (int)$db->query("SELECT COUNT(*) AS c FROM cs_audit_log WHERE $wSql")->fetch_assoc()['c'];
        $rows  = [];
        $res   = $db->query("SELECT * FROM cs_audit_log WHERE $wSql ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success'=>true,'data'=>$rows,'total'=>$total,'page'=>$page,'pages'=>(int)ceil($total/$limit)]);
        break;

    case 'who_am_i':
        echo json_encode([
            'success'   => true,
            'user_id'   => $_uid,
            'username'  => $_SESSION['css_user']  ?? '',
            'full_name' => $_SESSION['css_fullname'] ?? '',
            'role'      => $_SESSION['css_role']  ?? 'staff',
        ]);
        break;

    default:
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
}