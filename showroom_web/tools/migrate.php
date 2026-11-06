<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/config/db.php';
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$conn->query('CREATE TABLE IF NOT EXISTS app_migrations (ten VARCHAR(100) PRIMARY KEY, sha256 CHAR(64) NOT NULL, luc DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB');
foreach(glob(dirname(__DIR__).'/database/*.sql') as $file){
 $name=basename($file); $hash=hash_file('sha256',$file);
 $s=$conn->prepare('SELECT sha256 FROM app_migrations WHERE ten=?');$s->bind_param('s',$name);$s->execute();$old=$s->get_result()->fetch_assoc();
 if($old){if($old['sha256']!==$hash)throw new RuntimeException('Migration đã chạy bị thay đổi: '.$name);continue;}
 if($name==='01_tao_bang.sql'&&$conn->query("SHOW TABLES LIKE 'tai_khoan'")->num_rows){echo "Giữ 9 bảng gốc.\n";}
 else {$conn->multi_query(file_get_contents($file));do{if($r=$conn->store_result())$r->free();if(!$conn->more_results())break;}while($conn->next_result());}
 $s=$conn->prepare('INSERT INTO app_migrations(ten,sha256) VALUES (?,?)');$s->bind_param('ss',$name,$hash);$s->execute();echo 'Đã áp dụng '.$name.PHP_EOL;
}
