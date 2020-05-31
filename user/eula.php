<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Change password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once $CFG->libdir.'/authlib.php';
require_once($CFG->dirroot.'/user/lib.php');
//require_once '../lib/sis_lib.php'; //
//require_once '../lib/sis_lib.php'; //never allow sis_lib to be here because it will cause cyclic redirection for password redirect
require_once '../lib/sis_ui_lib.php'; 
require('lib.php');

$id     = optional_param('id', SITEID, PARAM_INT); // current course
$return = optional_param('return', 0, PARAM_BOOL); // redirect after password change

$systemcontext = context_system::instance();

//HTTPS is required in this page when $CFG->loginhttps enabled
$PAGE->https_required();

$urlparams = array('id' => $id);
$PAGE->set_url('/local/sis/user/eula.php', $urlparams);

$PAGE->set_context($systemcontext);

if ($return) {
    // this redirect prevents security warning because https can not POST to http pages
    if (empty($SESSION->wantsurl)
            or stripos(str_replace('https://', 'http://', $SESSION->wantsurl), str_replace('https://', 'http://', $CFG->wwwroot.'/login/change_password.php')) === 0) {
        $returnto = "$CFG->wwwroot/user/preferences.php?userid=$USER->id&course=$id";
    } else {
        $returnto = $SESSION->wantsurl;
    }
    unset($SESSION->wantsurl);

    redirect($returnto);
}

$strparticipants = get_string('participants');

if (!$course = $DB->get_record('course', array('id'=>$id))) {
    print_error('invalidcourseid');
}

require_login(); //always require login
// require proper login; 
if ($USER->auth != 'db') 
{
	$a_url = new moodle_url($CFG->httpswwwroot.'/index.php', array());
    redirect($a_url);
}
else if($USER->auth == 'db') //
{
	$user = $DB->get_record('si_user', array('id' => $USER->idnumber));
	$var_name = $user->user_type . '_require_eula';
	//cannot use get_config because we cannot include the global library due to the bootstraper
	//manually retrieve the variable
	$condition = array(
		'institute' => $user->institute,
		'name' => $var_name,
	);
	$result = $DB->get_record('si_config', $condition);
	if($result)
		$var_value = $result->var_value;
	else
		$var_value = '';		
	if($var_value == 'N')
	{
		$a_url = new moodle_url($CFG->httpswwwroot.'/index.php', array());
		redirect($a_url);
	}
}

$PAGE->set_context(context_user::instance($USER->id));
$PAGE->set_pagelayout('maintenance'); //set to maintenance where there is no redirect function to avoid circular redirection
$PAGE->set_course($course);

$PAGE->set_title(get_string('brand_name', 'local_sis'));
$PAGE->set_heading(get_string('brand_name', 'local_sis'));
$_SESSION['sis_home_tab'] = 'sis';
$PAGE->navbar->add(get_string('system', 'local_sis'), new moodle_url($CFG->wwwroot . '/index.php', array('tab' => 'system')));
$PAGE->navbar->add(get_string('eula', 'local_sis'), new moodle_url('eula.php'));

$navlinks = array();
$navlinks[] = array('name' => $strparticipants, 'link' => "$CFG->wwwroot/user/index.php?id=$course->id", 'type' => 'misc');

if (isset($_POST['accept'])) 
{	
	$user = $DB->get_record('si_user', array('id' => $USER->idnumber));
	$user->eula = 'A';
	$user->eula_date = time();
	$DB->update_record('si_user', $user);
	$_SESSION['sis_eula'] = 1; //accepted eula

    redirect($CFG->wwwroot.'/index.php');

}
if (isset($_POST['decline'])) 
{	
	$user = $DB->get_record('si_user', array('id' => $USER->idnumber));
	$user->eula = 'R';
	$user->eula_date = time();
	$DB->update_record('si_user', $user);
	$_SESSION['sis_eula'] = 3; //user rejected eula
	
	$sesskey = $USER->sesskey;
	$url = new moodle_url($CFG->wwwroot.'/login/logout.php', array('sesskey' => $sesskey));
	redirect($url);

}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

$strchange = get_string('eula', 'local_sis');

$fullname = fullname($USER, true);

$PAGE->set_title($strchange);
$PAGE->set_heading($fullname);
echo $OUTPUT->header();

sis_ui_page_title(get_string('eula', 'local_sis'));

$eula_msg = '
<div class="text-right" dir="rtl">
<h4>
  <p align="center" dir="rtl"><strong>عقد الإلتزام السلوكي </strong></p>
  <ul>
    <li><span dir="rtl"> </span>مقدمة:<span dir="ltr"> </span></li>
  </ul>
  <p dir="rtl">تعد العلاقات البناءة بين المعهد والطالب عاملاً حاسماً في ضمان تقديم  خبرات تعليمية نظرية وعملية فعالة للطلاب مرتكزة على توفير الدعم المناسب وتوفير  بيئة إيجابية بناءة مستندة إلى أحكام وشروط واضحة في صيغة عقد يلتزم به الطرفان  حسب لوائح وانظمة شؤون الطلاب.<br />
    إضافة إلى هذا العقد، ينبغي على المعهد تطبيق إجراءات فاعلة لمعالجة شكاوى  الطلاب وتحفظاتهم، كما ينبغي على الطلاب في الوقت عينه احترام قوانين وإجراءات المعهد.<br />
    تعد الأحكام والشروط المنصوص عليها في هذا العقد جزءا من دليل شؤون الطلاب  المعمول بها في المعهد العالي للصناعات المطاطية.</p>
  <ul>
    <li><span dir="rtl"> </span>تعاريف مهمة:</li>
  </ul>
  <p dir="rtl">&quot;الطالب&quot;: الطالب الذين يتم قبول إلتحاقه بالمعهد والمذكور أسمه  صراحة في هذا العقد.<span dir="ltr"> </span><br />
    <span dir="rtl"> </span><span dir="rtl"> </span><span dir="rtl"> </span><span dir="rtl"> </span>&quot;العقد&quot;: هو هذه الوثيقة الموقعة بين  المعهد والطالب.<br />
  &quot;&quot;ممثل المعهد&quot;: أحد موظفي المعهد المسؤول عن توقيع  العقد مع الطلاب.<br />
  &quot;السياسات&quot;: هي المفاهيم والمبادئ و/أو القواعد المُعتمدة في  المعهد، والتي يسعى المعهد إلى تعميمها على جميع الأطراف المعنية من أجل تحقيق أهداف  محددة.  وقد تكون هذه الأهداف ذات صلة بأي من  الجوانب المتعلقة بعمل المعهد، مثل الجانب الأكاديمي أو الجوانب المتعلقة بالصحة أو  السلامة أو سلوك الطلاب.<br />
  &quot;المعهد&quot;: يعني المعهد العالي للصناعات المطاطية بينبع<span dir="ltr"> </span></p>
  <ul>
    <li><span dir="rtl"> </span>المواقف والسلوكيات:<span dir="ltr"> </span></li>
  </ul>
  <p dir="rtl">على المعهد أن يسعى جاهداً لتوفر بيئة تعلم  خالية من المخاطر وآمنة نفسياً، على النحو الذي يتيح للطلاب تحقيق أقصى ما بوسعهم على  صعيدي التقدم الدراسي والتطور الشخصي. ولا تقع مسؤولية تحقيق هذا الهدف على عاتق المعهد  فقط، بل على الطلبة تحمل مسؤولياتهم في الجوانب التالية:</p>
  <ul>
    <li><span dir="rtl"> </span>الالتزام بسياسة السلوكيات  المعتمدة في المعهد والمرفقة مع هذا العقد.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>التأكد من أن الطالب يدرك  توقعات المعهد والعواقب المحتملة التي قد تنشأ عن أي مخالفة لقواعد السلوك المطبقة.<span dir="ltr"> </span></li>
  </ul>
  <p dir="rtl">يجب على جميع الطلاب  الالتزام بأنظمة ولوائح المعهد وبكل ما يصدر عنها من قواعد وإرشادات وإعلانات  وأعراف علمية وأكاديمية، وكذا الالتزام بالأنظمة والقواعد والآداب العامة وأعراف  وعادات وتقاليد وقيم مجتمعنا المنبثقة من روح الدين الإسلامي الحنيف وعليهم بصفة  خاصة الالتزام بالواجبات الآتية:</p>
  <ul>
    <li><span dir="rtl"> </span>‌كل ما ورد في اللائحة التنظيمية لشؤون الطلاب من أنظمة ولوائح.</li>
    <li><span dir="rtl"> </span>‌دفع المبالغ المالية المقررة سواءً كانت قيمة الخدمات الطلابية أو  غرامات أو خلافه.</li>
    <li><span dir="rtl"> </span>‌عدم محاولة تعطيل الدراسة أو التحريض على ذلك أو الامتناع المدبر عن  حضور الدروس والمحاضرات والأنشطة الأخرى التي تقضي اللوائح بالمواظبة عليها.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>حضور الطالب للمحاضرة وعدم التأخر عنها، والانضباط التام وعدم اثارة  الشغب بأي شكل من الاشكال<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>على الطالب الانصراف فورا من قاعة المحاضرة في حالة طلب المدرب منه  ذلك، وعدم الدخول في جدال في قاعة المحاضرة.</li>
    <li><span dir="rtl"> </span>‌عدم القيام&nbsp;– قولاً أو فعلاً – بما يتنافى مع الدين الإسلامي أو  الشرف أو الكرامة أو&nbsp;السيرة الطيبة والسلوك الحسن أو يمس سمعة المعهد بسوء في  الداخل أو الخارج.</li>
    <li><span dir="rtl"> </span>‌التقيد بنظم الاختبارات وإلتزام الهدوء فيها وعدم محاولة الغش أو  الشروع فيه. </li>
    <li><span dir="rtl"> </span>‌المحافظة على منشآت المعهد والأجهزة والمواد والكتب، وإرجاع ما  أستعير منها في الوقت المحدد بدون أي تبديد أو إتلاف. <span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>‌عدم تنظيم الجمعيات داخل المعهد أو الاشتراك فيها بدون ترخيص مسبق من  الجهات المختصة بالمعهد.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>عدم توزيع النشرات أو إصدار صحف حائطية أو نشر وإعلان أي منها، وعدم  جمع أموال أو توقيعات بدون ترخيص مسبق من الجهات المختصة بالمعهد.</li>
    <li><span dir="rtl"> </span>احترام كل منتسبي المعهد (هيئة تدريس وموظفين وطلاب) وعدم الإساءة إليهم  أو إهانتهم بالقول أو الفعل.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>عدم محاولة التغيير في الوثائق الرسمية الصادرة عن المعهد أو غير المعهد،  وكذا عدم حيازتها بطرق غير مشروعة.</li>
    <li><span dir="rtl"> </span>‌الامتثال للعقوبة التي توقع عليه من الجهات المختصة.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>‌عدم حمل أي نوع من أنواع الأسلحة بما في ذلك السلاح الأبيض أثناء  وجوده بالمعهد أو في مهمة لتمثيل المعهد.</li>
    <li><span dir="rtl"> </span>‌الحضور إلى المعهد أو في المهمات الخارجية بالزي المناسب الذي يكفل  الاحترام اللائق للمعهد. </li>
    <li><span dir="rtl"> </span>الاطلاع على كل ما ينشره المعهد من لوائح وإرشادات بما في ذلك ما ينشر  في موقع المعهد أو في لوحات الإعلانات بالمعهد وعدم جواز الاحتجاج بجهله بما ينشر فيها.</li>
    <li><span dir="rtl"> </span>‌الإيفاء بكل ما التزم به بعقد مكتوب يكون المعهد طرفاً فيه.<span dir="ltr"> </span></li>
  </ul>
  <ul>
    <li><span dir="rtl"> </span>الصحة والسلامة:<span dir="ltr"> </span></li>
  </ul>
  <p dir="rtl">إن توفير بيئة تدريبية آمنة وصحية هي مسؤولية  المعهد و المتدربين، وبناء عليه يقع على عاتق المعهد إعداد وتنفيذ جميع السياسات والإجراءات  اللازمة، في حين يتوقع من المتدرب الالتزام بتطبيق سياسيات و إجراءات السلامة في  المعهد والتأكد من الإتزام  بتطبيقها.<br />
    تتضمن مسؤوليات المتدرب الجوانب التالية:</p>
  <ul>
    <li><span dir="rtl"> </span>إبلاغ المعهد بجميع المعلومات  المتعلقة بظروفه الطبية إن وجدت.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>الإبلاغ عن اي  سلوكيات او تصرفات تخل بأمن وسلامة المعهد و المتدربين.<span dir="ltr"> </span></li>
  </ul>
  <ul>
    <li><span dir="rtl"> </span>إجراءات فض المنازعات:</li>
  </ul>
  <p dir="rtl">يضمن المعهد للمتدربين الوصول إلى قرارات عادلة  ومنصفة فيما يتعلق بالمسائل الخلافية و المشكلات داخل المعهد. ولا ريب أن أفضل وسيلة  لحل هذه المشكلات هي من خلال اللقاءات والمناقشات غير الرسمية، إلا أن بعض المشكلات  والشكاوى قد تحتاج الإعتماد على انظمة و إجراءات المعهد.<br />
    يتوقع من المتدرب الرجوع إلى هذه العملية لتسوية  أية منازعات داخل المعهد.</p>
  <ul>
    <li><span dir="rtl"> </span> إذا كان الطالب يشكك في صحة قرار  اتخذه المعهد على أي مستوى، ينبغي عندها على المتدرب الرجوع الى مسؤول شؤون الطلاب  و إتباع الإجراء المعمول به.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>في حال استمرار عدم رضا الطالب، ينبغي عليه الاجتماع بوكيل المعهد  لإيجاد الحل المنصف.<span dir="ltr"> </span></li>
    <li><span dir="rtl"> </span>إذا لم يتم حل المشكلة، ينبغي عندها على الطالب كتابة خطاب موجه الى  مدير المعهد لمحاولة حل المشكلة.</li>
  </ul>
  <p align="center" dir="rtl"><strong><u>إقرار</u></strong><strong><u><span dir="ltr"> </span></u></strong><br />
    أنا المتدرب /   .....................................................  ورقمي الأكاديمي ...................</p>
<span dir="rtl"> قد اطلعت على محتوى عقد الإلتزام السلوكي وحضور  الإجتماع الخاص به وأتعهد على نفسي الإلتزام بالأخلاق الفاضلة، واحترام أنظمة  المعهد، وتعليماته، والتقيد بكل ما جاء في العقد من تعليمات، وأقر بأنه إذا حصل  مني ما يخل بالنظام سأتحمل مسؤولية ذلك، وفق ما تنص عليه القواعد، وعليه جرى  التوقيع والالتزا</span>
</h4>
</div>
';

echo $eula_msg;

$url = new moodle_url($CFG->wwwroot . '/index.php');
$form = '<div class="pt-3 text-center">
<form name="form1" method="post" action="">

	<input type="submit" name="accept" value="' . get_string('accept', 'local_sis') . '" class="btn btn-primary" />' . sis_ui_space(1) . '
	<input type="submit" name="decline" value="' . get_string('decline', 'local_sis') . '"  class="btn btn-primary" />
</form>
</div>';	

echo $form;
//sis_ui_alert($msg, 'danger', 'Note', false, false);		
echo $OUTPUT->footer();
