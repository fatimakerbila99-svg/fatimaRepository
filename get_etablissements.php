<?php
require_once 'db.php';
header('Content-Type: application/json');
$commune_id = isset($_GET['commune_id']) ? intval($_GET['commune_id']) : 0;
if($commune_id > 0) {
    $data = supabase_request('etablissements?commune_id=eq.' . $commune_id . '&order=nom_fr');
    echo json_encode($data);
} else {
    echo json_encode([]);
}
?>
