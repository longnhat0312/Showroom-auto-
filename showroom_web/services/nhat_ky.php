<?php
function audit(string $action,string $type,string $id,?array $before=null,?array $after=null,?int $actor=null): void {
    $actor??=$_SESSION['user_id']??null;
    foreach(['password','mfa_secret','hash_secret','secret','otp_hash'] as $key){unset($before[$key],$after[$key]);}
    execute('INSERT INTO nhat_ky (nguoi_thao_tac_id,hanh_dong,doi_tuong,ma_doi_tuong,truoc_json,sau_json) VALUES (?,?,?,?,?,?)',[$actor,$action,$type,$id,$before?json_encode($before,JSON_UNESCAPED_UNICODE):null,$after?json_encode($after,JSON_UNESCAPED_UNICODE):null]);
}
