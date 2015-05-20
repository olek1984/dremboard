<?php
/**
 * Template Name: One Page Template
 */
global $tpl, $countries;
$arrayofcountries = $countries->load_countries_from_xml();

$owner_id = bp_loggedin_user_id();
$owner = bp_core_get_core_userdata($owner_id);

gk_load('header');

gk_load('before');

wp_localize_script('rtmedia-backbone', 'sign_name', bp_get_loggedin_user_fullname());

?>
<style>
    #gk-sidebar{
        display:none!important;
    }
    #gk-mainbody-columns{
        background: none;
    }
    #gk-mainbody-columns > section{
        width: 100%;
    }

</style>
<?php
if (isset($_GET['action']) && 'counter_claim' == $_GET['action']) {
    ?>
    <section id="gk-mainbody">
        <form name="counter-claim-form" id="copy-right-form" action="" method="post">
            <input type="hidden" name="action" value="counter-claim">
            <section class="content license_page">
                <div class="license-container">
                    <div class="license-title">
                        <h1>Counter Claim Notification</h1>
                    </div>
                    <div class="license-content">
                        <div class="sub-container">
                            <div class="sub-item">
                                <p>
                                    Use this form to counter claim, if you believe you have the rights to post the content at issue.
                                </p>
                            </div>
                        </div>
                        <div class="sub-container agent_info">
                            <div class="sub-item row">
                                <strong>Contact Information </strong><em>(For person who file a counter claim)</em>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Name
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_name" class="required row-100" value="<?php echo (isset($owner)) ? $owner->display_name : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_street_address" class="row-50" placeholder="123 Msin St."/>
                                    <input type="text" name="agent_city" class="row-50 float-right" placeholder="San Francisco"/>
                                    <input type="text" name="agent_state" class="row-50" placeholder="CA"/>
                                    <input type="text" name="agent_zipcode" class="row-50 float-right" placeholder="94110"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Country
                                </div>
                                <div class="row-70">
                                    <select name="agent_country" class="row-100 country">
                                        <?php
                                        for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) - 1; $numerofcountries++) {
                                            $values = wp_parse_args($arrayofcountries[$numerofcountries][0], array(
                                                'countrycode' => '',
                                                'countryname' => ''
                                            ));
                                            $selected = ($values['countrycode'] == 'US') ? "selected" : "";
                                            echo '<option ' . $selected . ' value="' . $values['countrycode'] . '">' . $values['countryname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Phone Number
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_phone" class="phone_number row-100" placeholder="555-555-5555"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Email Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_email" class="email_addr required row-100" placeholder="" value="<?php echo (isset($owner)) ? $owner->user_email : ''; ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <button class="float-right removeItemButton">Remove Item</button>
                            <button class="float-right addAnotherButton">Add Another</button>
                            <div class="empty-line"></div>
                        </div>
                        <div class="group sub-form-template">
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Case Id</strong></p>
                                    <p>It is provided with email title sent to you as #Case:123</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="case_id[]" class="integer_input required row-100" placeholder="123"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Identify the infringed work on Drēmboard</strong></p>
                                    <p>Please provide the full URL to each individual Drēm, e.g. http://dremboard.com/activity/12345.</p>
                                    <p>Limit: 100 per request(copy and paste the link in the notification email)</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="reported_content_url[]" class="url_input required row-100" placeholder="http://dremboard.com/activity/12345"  value="<?php echo (isset($drem_url)) ? $drem_url : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <p><strong>Select the reason for your dispute</strong></p>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="mis_identify">1. This drēm does not feature the the third-party copyrighted material at issue. My drēm was misidentified as containing this material.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="mis_identify" name="reason[]" class="float-right" value="mis_identify" checked="checked"/>
                                </div>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="fair_use">2. This drēm uses copyrighted material in a manner that does not require approval of the copyright holder. It is a fair use under copyright law.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="fair_use" name="reason[]" class="float-right" value="fair_use"/>
                                </div>
                                <div class="row-100">
                                    <strong>Please explain briefly: </strong>
                                    <input type="text" id="fair_use_brief" name="fair_use_brief[]" size='80'/>
                                </div>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="appropriate">3. This drēm uses the copyrighted material at issue, but with the appropriate authorization from the copyright owner.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="appropriate" name="reason[]" class="float-right" value="appropriate"/>
                                </div>
                                <div class="row-100">
                                    <strong>Please explain briefly: </strong>
                                    <input type="text" id="appropriate_brief" name="appropriate_brief[]" size='80'/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <p><strong>Statement of Good Faith</strong></p>
                            <textarea id='good_faith' name='good_faith' class="good_faith required row-70"></textarea>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-100">
                                <p><strong>Type the following statement into the box above</strong></p>
                                <p class="good_faith_copy">I consent to the jurisdiction of the Federal District Court for the district in which my address is located, or if my address is outside of the United States, the judicial district in which drēm is located, and will accept service of process from the claimant.</p>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <strong>
                                Typing your name in this box acts as your electronic signature
                            </strong>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-30">
                                Your Name
                            </div>
                            <div class="row-70">
                                <input type="text" name="signed_name" class="elec_sign required row-100" placeholder=""/>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container submit disabled">
                        <div class="sub-item row">
                            <button class="float-right submit disabled">Submit</button>
                            <div class="empty-line"></div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </section>

    <?php
} else if (isset($_GET['action']) && 'dispute_counter' == $_GET['action']) {
    ?>
    <section id="gk-mainbody">
        <form name="dispute_counter-form" id="copy-right-form" action="" method="post">
            <input type="hidden" name="action" value="dispute-counter">
            <section class="content license_page">
                <div class="license-container">
                    <div class="license-title">
                        <h1>Dispute Counter Notification</h1>
                    </div>
                    <div class="license-content">
                        <div class="sub-container">
                            <div class="sub-item">
                                <p>
                                    Use this form to dispute counter, if you believe you have the rights to post the content at issue.
                                </p>
                            </div>
                        </div>
                        <div class="sub-container agent_info">
                            <div class="sub-item row">
                                <strong>Contact Information </strong><em>(For person who file a dispute counter)</em>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Name
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_name" class="required row-100" value="<?php echo (isset($owner)) ? $owner->display_name : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_street_address" class="row-50" placeholder="123 Msin St."/>
                                    <input type="text" name="owner_city" class="row-50 float-right" placeholder="San Francisco"/>
                                    <input type="text" name="owner_state" class="row-50" placeholder="CA"/>
                                    <input type="text" name="owner_zipcode" class="row-50 float-right" placeholder="94110"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Country
                                </div>
                                <div class="row-70">
                                    <select name="owner_country" class="row-100 country">
                                        <?php
                                        for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) - 1; $numerofcountries++) {
                                            $values = wp_parse_args($arrayofcountries[$numerofcountries][0], array(
                                                'countrycode' => '',
                                                'countryname' => ''
                                            ));
                                            $selected = ($values['countrycode'] == 'US') ? "selected" : "";
                                            echo '<option ' . $selected . ' value="' . $values['countrycode'] . '">' . $values['countryname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Phone Number
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_phone" class="phone_number row-100" placeholder="555-555-5555"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Email Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_email" class="email_addr required row-100" placeholder="" value="<?php echo (isset($owner)) ? $owner->user_email : ''; ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <button class="float-right removeItemButton">Remove Item</button>
                            <button class="float-right addAnotherButton">Add Another</button>
                            <div class="empty-line"></div>
                        </div>
                        <div class="group sub-form-template">
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Case Id</strong></p>
                                    <p>It is provided with email title sent to you as #Case:123</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="case_id[]" class="integer_input required row-100" placeholder="123"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Identify the infringed work on Drēmboard</strong></p>
                                    <p>Please provide the full URL to each individual Drēm, e.g. http://dremboard.com/activity/12345.</p>
                                    <p>Limit: 100 per request(copy and paste the link in the notification email)</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="reported_content_url[]" class="url_input required row-100" placeholder="http://dremboard.com/activity/12345"  value="<?php echo (isset($drem_url)) ? $drem_url : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <p><strong>Select the reason for your dispute</strong></p>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="mis_identify">1. This drēm does not feature the the third-party copyrighted material at issue. My drēm was misidentified as containing this material.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="mis_identify" name="reason[]" class="float-right" value="mis_identify" checked="checked"/>
                                </div>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="fair_use">2. This drēm uses copyrighted material in a manner that does not require approval of the copyright holder. It is a fair use under copyright law.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="fair_use" name="reason[]" class="float-right" value="fair_use"/>
                                </div>
                                <div class="row-100">
                                    <strong>Please explain briefly: </strong>
                                    <input type="text" id="fair_use_brief" name="fair_use_brief[]" size='80'/>
                                </div>
                            </div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <label for="appropriate">3. This drēm uses the copyrighted material at issue, but with the appropriate authorization from the copyright owner.</label>
                                </div>
                                <div class="row-30">
                                    <input type='radio' id="appropriate" name="reason[]" class="float-right" value="appropriate"/>
                                </div>
                                <div class="row-100">
                                    <strong>Please explain briefly: </strong>
                                    <input type="text" id="appropriate_brief" name="appropriate_brief[]" size='80'/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <p><strong>Statement of Good Faith</strong></p>
                            <textarea id='good_faith' name='good_faith' class="good_faith required row-70"></textarea>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-100">
                                <p><strong>Type the following statement into the box above</strong></p>
                                <p class="good_faith_copy">I consent to the jurisdiction of the Federal District Court for the district in which my address is located, or if my address is outside of the United States, the judicial district in which drēm is located, and will accept service of process from the claimant.</p>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <strong>
                                Typing your name in this box acts as your electronic signature
                            </strong>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-30">
                                Your Name
                            </div>
                            <div class="row-70">
                                <input type="text" name="signed_name" class="elec_sign required row-100" placeholder=""/>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container submit disabled">
                        <div class="sub-item row">
                            <button class="float-right submit disabled">Submit</button>
                            <div class="empty-line"></div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </section>

    <?php
}else{

    if (isset($_POST['activity_id'])) {
        $activity_id = $_POST['activity_id'];
        $activity = new BP_Activity_Activity($activity_id);
        $agent_id = $activity->user_id;
        $agent = bp_core_get_core_userdata($agent_id);
        $drem_url = home_url(bp_get_activity_root_slug() . '/' . $activity_id);
    }


    ?>

    <section id="gk-mainbody">
        <form name="copy-right-form" id="copy-right-form" action="" method="post">
            <section class="content license_page">
                <div class="license-container">
                    <div class="license-title">
                        <h1>Copyright Infringement Notification</h1>
                    </div>

                    <div class="license-content">
                        <div class="sub-container">
                            <div class="sub-item">
                                <p>
                                    Use this form to identify content on Drēmboard that you want removed based on alleged infringement of your copyrights.
                                </p>
                            </div>
                        </div>
                        <div class="sub-container agent_info">
                            <div class="sub-item row">
                                <strong>Contact Information </strong><em>(For person who posted copyright content)</em>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Name
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_name" class="required row-100" value="<?php echo (isset($agent)) ? $agent->display_name : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_street_address" class="row-50" placeholder="123 Msin St."/>
                                    <input type="text" name="agent_city" class="row-50 float-right" placeholder="San Francisco"/>
                                    <input type="text" name="agent_state" class="row-50" placeholder="CA"/>
                                    <input type="text" name="agent_zipcode" class="row-50 float-right" placeholder="94110"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Country
                                </div>
                                <div class="row-70">
                                    <select name="agent_country" class="row-100 country">
                                        <?php
                                        for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) - 1; $numerofcountries++) {
                                            $values = wp_parse_args($arrayofcountries[$numerofcountries][0], array(
                                                'countrycode' => '',
                                                'countryname' => ''
                                            ));
                                            $selected = ($values['countrycode'] == 'US') ? "selected" : "";
                                            echo '<option ' . $selected . ' value="' . $values['countrycode'] . '">' . $values['countryname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Phone Number
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_phone" class="phone_number row-100" placeholder="555-555-5555"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Email Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="agent_email" class="email_addr required row-100" placeholder="" value="<?php echo (isset($agent)) ? $agent->user_email : ''; ?>"/>
                                </div>
                            </div>
                        </div>
                        <div class="sub-container owner_info">
                            <div class="sub-item row">
                                <strong>Contact Information </strong><em>(For rights owner)</em>
<!--                                <button class="sameInfoButton float-right">Same As Above</button>-->
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Name
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_name" class="required row-100" value="<?php echo (isset($owner)) ? $owner->display_name : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_street_address" class="row-50" placeholder="123 Msin St."/>
                                    <input type="text" name="owner_city" class="row-50 float-right" placeholder="San Francisco"/>
                                    <input type="text" name="owner_state" class="row-50" placeholder="CA"/>
                                    <input type="text" name="owner_zipcode" class="row-50 float-right" placeholder="94110"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Country
                                </div>
                                <div class="row-70">
                                    <select name="owner_country" class="row-100 country">
                                        <?php
                                        for ($numerofcountries = 0; $numerofcountries <= count($arrayofcountries) - 1; $numerofcountries++) {
                                            $values = wp_parse_args($arrayofcountries[$numerofcountries][0], array(
                                                'countrycode' => '',
                                                'countryname' => ''
                                            ));
                                            $selected = ($values['countrycode'] == 'US') ? "selected" : "";
                                            echo '<option ' . $selected . ' value="' . $values['countrycode'] . '">' . $values['countryname'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Phone Number
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_phone" class="phone_number row-100" placeholder="555-555-5555"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30 label">
                                    Email Address
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_email" class="email_addr required row-100" placeholder="" value="<?php echo (isset($owner)) ? $owner->user_email : ''; ?>"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <button class="float-right removeItemButton">Remove Item</button>
                            <button class="float-right addAnotherButton">Add Another</button>
                            <div class="empty-line"></div>
                        </div>
                        <div class="group sub-form-template">
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Identify your work on your own website</strong></p>
                                    <p>Please provide URLs, e.g. http://mysite.com/products/1.</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="owner_website_url[]" class="url_input required row-100" placeholder="http://mysite.com/products/1"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-30">
                                    <p><strong>Identify the infringed work on Drēmboard</strong></p>
                                    <p>Please provide the full URL to each individual Drēm, e.g. http://dremboard.com/activity/12345.</p>
                                    <p>Limit: 100 per request</p>
                                </div>
                                <div class="row-70">
                                    <input type="text" name="reported_content_url[]" class="url_input required row-100" placeholder="http://dremboard.com/activity/12345"  value="<?php echo (isset($drem_url)) ? $drem_url : ''; ?>"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <p><strong>Remove All</strong></p>
                                    <p>You can request the removal of only this specific Drēm, or all Drēms that contain the same image file. Please note that only identical copies of the image file can be removed by this function. If an image file has been re-sized or altered in any other way, then it can not be detected or removed through this function.</p>
                                    <br>
                                    <p>By clicking yes, you are asking Drēmboard to remove all Drēms containing the same image file</p>
                                </div>
                                <div class='row-30'>
                                    <input type="hidden" name="remove_all[]" value="0" />
                                    <input type='checkbox' class="remove_all float-right" value="0"/>
                                </div>
                            </div>
                            <div class="divider"></div>
                            <div class="sub-item row">
                                <div class="row-70">
                                    <p><strong>Strike</strong></p>
                                    <p>Drēmboard enforces a repeat infringer policy that may result in the termination of users who acquire multiple strikes as a result of copyright complaints. Drēmboard recognizes that some copyright owners may not want their takedown notices to result in the assignment of a strike.</p>
                                    <br>
                                    <p>By clicking yes, you are asking Drēmboard to assign a strike against the user who posted the image you identified in the URL above.</p>
                                </div>
                                <div class='row-30'>
                                    <input type="hidden" name="strike[]" value="0" />
                                    <input type='checkbox' class="strike float-right" value="0"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <strong>By checking the following boxes, I confirm:</strong>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-70">
                                <p>The information in this notice is accurate.</p>
                            </div>
                            <div class="row-30">
                                <input type="hidden" name="is_accurate" value="0" />
                                <input type='checkbox' class="is_accurate required float-right"/>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-70">
                                <p>I have a good faith belief that the disputed use of the copyrighted material is not authorized by the copyright owner, its agent, or the law (e.g., as a fair use).</p>
                            </div>
                            <div class="row-30">
                                <input type="hidden" name="is_good_faith" value="0" />
                                <input type='checkbox' class="is_good_faith required float-right"/>
                            </div>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-70">
                                <p>I state under penalty of perjury that I am the owner, or authorized to act on behalf of the owner, of the copyright or of an exclusive right under the copyright that is allegedly infringed.</p>
                            </div>
                            <div class="row-30">
                                <input type="hidden" name="is_authorized_agent" value="0" />
                                <input type='checkbox' class="is_authorized_agent required float-right"/>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container">
                        <div class="sub-item row">
                            <strong>
                                Typing your name in this box acts as your electronic signature
                            </strong>
                        </div>
                        <div class="divider"></div>
                        <div class="sub-item row">
                            <div class="row-30">
                                Your Name
                            </div>
                            <div class="row-70">
                                <input type="text" name="signed_name" class="elec_sign required row-100" placeholder=""/>
                            </div>
                        </div>
                    </div>
                    <div class="sub-container submit disabled">
                        <div class="sub-item row">
                            <button class="float-right submit disabled">Submit</button>
                            <div class="empty-line"></div>
                        </div>
                    </div>
                </div>
            </section>
        </form>
    </section>

    <?php
}

gk_load('after');

gk_load('footer');

// EOF