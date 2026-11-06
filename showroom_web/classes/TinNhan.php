<?php class TinNhan {public static function gui(array $u,int $id,string $body):int{require_once APP_ROOT.'/services/chat.php';return send_chat($u,$id,$body);}}
