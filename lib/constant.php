<?php
// key
define ('SITE_ENC_KEY', 'ejtnuakRDtYf8TQm');
define ('SITE_NAME', 'ETALKING');

define ('DATE_RANGE', 28);
//define ('BEGIN_TIME', 14);
//define ('END_TIME', 22);

define ('WEBEX_TEST_MAX_QYT',9);

define ('DOC_STATUS_SHOW', 10);
define ('DOC_STATUS_HIDE', 0);

//define ('POINT_1on1', 3);
//define ('POINT_GROUP', 1);
define ('GROUP_PERSONS', 6);
define ('ELECTIVE_PERSONS', 6);
define ('HALL_PERSONS', 25);

define ('CONSTRAINT_UNIQUE', 10);

define ('SN_ROOM', 'R');
define ('SN_CONTRACT', 'C');
define ('SN_BILL', 'B');
define ('SN_MATERIAL', 'M');

$var_doc_status = array (
  DOC_STATUS_HIDE => '隱藏',
  DOC_STATUS_SHOW => '顯示',
);

$var_doc_status_color = array (
  DOC_STATUS_HIDE => 'blue',
  DOC_STATUS_SHOW => 'black',
);

$var_general_status = $var_doc_status;
$var_general_status_color = $var_doc_status_color;

$var_doc_yesno = array (
  '0' => '否',
  '1' => '是',
);

$var_doc_yesno_color = array (
  '0' => 'gray',
  '1' => 'blue',
);

$var_general_yesno = $var_doc_yesno;
$var_general_yesno_color = $var_doc_yesno_color;

$var_consultant_type = array (
  10 => '中師',
  20 => '外師',
);

$var_contract_type = array (
  10 => '學員合約',
  20 => '顧問合約',
);

$var_contract_status = array (
  10 => '審核中',
  20 => '核可',
  30 => '退回',
  90 => '取消',
  100 => '已逾期',
);

$var_period = array (
  3 => '3',
  6 => '6',
  12 => '12',
  18 => '18',
  24 => '24',
  30 => '30',
  36 => '36',
);

$var_installment = array (
  3 => '3',
  6 => '6',
  12 => '12',
);

$var_member_gender = array (
  //0 => '不設定',
  1 => '男',
  2 => '女',
);

$var_contact_time = array (
  8 => '08:00~11:00',
  11 => '11:00~14:00',
  14 => '14:00~18:00',
  18 => '18:00~22:00'
);

$var_member_gender_color = array (
  0 => 'black',
  1 => 'blue',
  2 => 'red',
);

$var_product_status = array (
  '30' => '現貨',
  '20' => '缺貨',
  '10' => '下架',
);

$var_product_status_color = array (
  '10' => 'danger',
  '20' => 'warning',
  '30' => 'success',
);

$var_member_history_type = array (
  10 => '指派',
  20 => '聯絡',
  30 => '預約DEMO',
  31 => '取消DEMO',
  32 => '完成DEMO',
  50 => '釋出',
  60 => '開發',
  70 => '客服',
  31 => '取消DEMO',
  80 => '電腦測試',
);

$var_member_type = array (
  10 => '國內',
  20 => '國外',
);

$var_member_history_subtype = array (
  10 => '其他原因',
  20 => '更換設備',
  30 => '首次環測',
);

$var_course_attend = array (
  0 => '未出席',
  10 => '已出席',
);

$var_registration_type = array (
  10 => 'DEMO',
  20 => '一對一', // 自由
  30 => '小班制', // 自由
  40 => '選修制',
  50 => '大會堂',
  99 => '薪資調整',
);

$var_registration_type_short = array (
  10 => 'Ｄ',
  20 => '一', // 自由
  30 => '小', // 自由
  40 => '選',
  50 => '大',
);

$var_registration_type_eng = array (
  10 => 'DEMO',
  20 => 'One-On-One', // 自由
  30 => 'Group Class', // 自由
  40 => 'Selctive Class Lesson',
  50 => 'Lecture Hall',
);

$var_member2_status = array (
  60 => '開發中',
  70 => '釋出',
);

$var_classroom_status = array (
  10 => '正常',
  20 => '取消',
);

$var_classroom_status2 = array (
  10 => '進入教室',
  20 => '完成'
);

for ($i = BEGIN_TIME; $i <= END_TIME; $i++)
{
  $h = sprintf("%02d", $i);
  $var_schedule_time[$i] = "$h:00 ~ $h:45";
}
unset ($h);
//for ($i = 0; $i <= 23; $i++)
//{
//  $h = sprintf("%02d", $i);
//  $var_schedule_time[] = "$h:00 ~ $h:45";
//}
//unset ($h);

$var_schedule_available_type = array (
  0 => '',
  10 => 'Available', // fixed
  20 => 'Available', // custom
  30 => 'DEMO', // for DEMO
);

$var_schedule_available_type_short = array (
  0 => '',
  10 => '固', // fixed
  20 => '自', // custom
  30 => 'Ｄ', // for DEMO
);

$var_member_status = array (
  '10' => '啟用',
  '20' => '停權',
);

$var_member_status_color = array (
  '20' => 'danger',
  '10' => 'success',
);

$var_member_status2 = array (
  '10' => '啟用',
  '20' => '停權',
  '30' => '未啟用',
);

$var_member_status_color2 = array (
  '10' => 'success',
  '20' => 'danger',
  '30' => 'primary',
);

$var_payment = array (
  '10' => '轉帳',
  '20' => '刷卡',
  '30' => '分期',
  '40' => '現金',
  '50' => '信貸',
//  '99' => '自訂',
);

$var_payment2 = array (
  '10' => 'ATM轉帳',
  '20' => '線上信用卡一次付清',
  '30' => '線上信用卡分期付款',
  '40' => '現金',
  '50' => '零息分期',
//  '99' => '',
);

$var_paid = array (
  '1' => '未結帳',
  '2' => '未付款',
  '3' => '付款失敗',
  '4' => '已付款',
);

$var_paid_color = array (
  '1' => 'white',
  '2' => 'white',
  '3' => 'danger',
  '4' => 'success',
);

$var_service_status = array (
  '10' => '待回覆',
  '20' => '已回覆',
);

$var_service_status_color = array (
  '10' => 'white',
  '20' => 'blue',
  '30' => 'green',
);

$var_education = array (
  10 => 'Senior High',
  20 => 'Bachelor',
  30 => 'Master',
  40 => 'Doctor',
);

$var_language = array (
  0 => '',
  1 => 'English',
  2 => 'Chinese',
);

$var_weekday = array (
  0 => 'Sunday',
  1 => 'Monday',
  2 => 'Tuesday',
  3 => 'Wednesday',
  4 => 'Thursday',
  5 => 'Friday',
  6 => 'Saturday',
);

$var_weekday_short = array (
  0 => 'Sun',
  1 => 'Mon',
  2 => 'Tue',
  3 => 'Wed',
  4 => 'Thu',
  5 => 'Fri',
  6 => 'Sat',
);

$var_demo_payment = array (
  0 => '不能DEMO',
  10 => '以堂數計',
  20 => '以次數計',
);

$var_member_education = array (
  10 => '國小',
  20 => '國中',
  30 => '高中/職',
  40 => '大專院校',
  50 => '碩士',
  60 => '博士',
);

$var_point_type = array (
  10 => '預約課程', // course_registration
  20 => '新增合約', // member_contract
  100 => '取消課程', // course_registration
  110 => '補償',
  120 => '取消合約', // member_contract
  130 => '退還點數',  // course_registration
  140 => '異動點數',
);

$var_questionnaire_type = array (
  10 => 'DEMO',
  20 => '學生對老師',
  30 => '老師對學生',
);

$var_cc_type = array (
  10 => '歐付寶金流',
  20 => 'EZpay金流',
);

$var_material_type = array (
  10 => '自由選課',
  20 => '選修課程',
  30 => '大會堂',
);

$var_webex_type = array (
   5 => '環境測試',
  10 => 'DEMO專用',
  20 => '一般課程',
  30 => '大會堂',
);

$EMAIL_SUBJECT_WEBEXTEST = '環境測試預約通知';
$EMAIL_SUBJECT_WEBEXTEST_CANCEL = '環境測試預約取消通知';
$EMAIL_SUBJECT_DEMO = 'DEMO預約通知';
$EMAIL_SUBJECT_REQUEST = '免費體驗需求通知';
$EMAIL_SUBJECT_NEW_MEMBER = '新用戶通知';
$EMAIL_SUBJECT_NEW_USER = '新帳號通知';
$EMAIL_SUBJECT_CONTACT_US = '連絡通知';
$EMAIL_SUBJECT_REPORT = '報表完成通知';
?>