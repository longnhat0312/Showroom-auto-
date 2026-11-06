<?php
require_once __DIR__.'/vnpay.php';require_once __DIR__.'/thanh_toan.php';
function vnp_config(): array {$c=private_config('vnpay');must(!empty($c['tmn_code'])&&!empty($c['hash_secret'])&&!empty($c['return_url'])&&!str_contains($c['tmn_code'],'DIEN_'),'VNPay chưa được cấu hình. Quản trị viên cần nhập TmnCode, HashSecret và Return URL.');$c['payment_url']='https://sandbox.vnpayment.vn/paymentv2/vpcpay.html';return $c;}
function start_vnpay(array $u,string $id): string {
 $c=vnp_config();$p=transaction(function()use($u,$id){[$h,$x]=lock_contract($id);must($u['quyen']==='KHACH_HANG'&&(int)$h['tai_khoan_id']===(int)$u['id'],'Đơn không thuộc tài khoản.',403);must($h['trang_thai_hd']==='CHO_COC'&&strtotime($h['han_giu_vin'])>time(),'Phiên đặt cọc đã hết hạn hoặc đơn đã nhận đủ cọc.');$amount=max(0,vnd($h['tien_dat_coc'])-money_total($id));must($amount>0,'Đã nhận đủ tiền cọc.');$old=db_one("SELECT * FROM hoa_don WHERE so_hop_dong=? AND nguon_tham_chieu='VNPAY_SANDBOX' AND trang_thai='CHO_XAC_NHAN' AND het_han_phien>NOW() ORDER BY tao_phien_luc DESC LIMIT 1 FOR UPDATE",[$id]);if($old)return $old;
 $pay=uid('TT');execute("INSERT INTO hoa_don(ma_hoa_don,so_hop_dong,so_tien_thu,phuong_thuc_tt,nguon_tham_chieu,ma_phien_tt,het_han_phien,tao_phien_luc,noi_dung_thu) VALUES(?,?,?,'CONG_THANH_TOAN','VNPAY_SANDBOX',?,?,NOW(),'Thanh toán đặt cọc qua VNPay Sandbox')",[$pay,$id,$amount,$pay,$h['han_giu_vin']]);return db_one('SELECT * FROM hoa_don WHERE ma_hoa_don=?',[$pay]);});
 return vnp_payment_url($c,$p['ma_hoa_don'],(string)$p['so_tien_thu'],$_SERVER['REMOTE_ADDR']??'127.0.0.1',new DateTimeImmutable($p['het_han_phien'],new DateTimeZone('Asia/Ho_Chi_Minh')),new DateTimeImmutable($p['tao_phien_luc'],new DateTimeZone('Asia/Ho_Chi_Minh')));
}
function vnpay_notification(array $data): array {
 try{$c=vnp_config();}catch(AppError){return ['RspCode'=>'99','Message'=>'Configuration unavailable'];}
 if(!vnp_verify($data,$c['hash_secret']))return ['RspCode'=>'97','Message'=>'Invalid signature'];
 if(($data['vnp_TmnCode']??'')!==$c['tmn_code'])return ['RspCode'=>'97','Message'=>'Invalid merchant'];
 $p=db_one("SELECT * FROM hoa_don WHERE ma_hoa_don=? AND nguon_tham_chieu='VNPAY_SANDBOX' AND phuong_thuc_tt='CONG_THANH_TOAN' AND loai_giao_dich='THU'",[$data['vnp_TxnRef']??'']);if(!$p)return ['RspCode'=>'01','Message'=>'Order not found'];
 if(!preg_match('/^[0-9]+$/D',$data['vnp_Amount']??'')||ltrim($data['vnp_Amount'],'0')!==vnp_amount($p['so_tien_thu']))return ['RspCode'=>'04','Message'=>'Invalid amount'];
 if(isset($data['vnp_CurrCode'])&&$data['vnp_CurrCode']!=='VND')return ['RspCode'=>'04','Message'=>'Invalid currency'];
 $success=($data['vnp_ResponseCode']??'')==='00'&&($data['vnp_TransactionStatus']??'')==='00';
 $when=date('Y-m-d H:i:s');$ref=$data['vnp_TransactionNo']??'';
 if($success){$date=DateTimeImmutable::createFromFormat('!YmdHis',$data['vnp_PayDate']??'');if(!$date||$date->format('YmdHis')!==$data['vnp_PayDate']||!preg_match('/^[0-9]{1,100}$/D',$ref)||$ref==='0')return ['RspCode'=>'99','Message'=>'Invalid transaction data'];$when=$date->format('Y-m-d H:i:s');}
 if(!$success&&!in_array($data['vnp_TransactionStatus']??'',['02','03','05','06','09'],true)&&($data['vnp_ResponseCode']??'')!=='24')return ['RspCode'=>'00','Message'=>'Pending notification received'];
 try{$result=settle_payment($p['ma_hoa_don'],vnd($p['so_tien_thu']),$ref,'VNPAY_SANDBOX',$success,$when);return ['RspCode'=>$result==='ALREADY'?'02':'00','Message'=>$result==='ALREADY'?'Already confirmed':'Confirm success'];}catch(Throwable $e){error_log('VNPay settlement code '.$e->getCode());return ['RspCode'=>'99','Message'=>'Processing error'];}
}
