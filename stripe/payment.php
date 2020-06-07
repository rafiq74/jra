<div class="mdl-align">
<h3><?php echo $instance->name; ?></h3>
<div class="stripe-img"><img src="<?php echo $CFG->wwwroot; ?>/local/jra/stripe/stripe_powered_by.png" width="200" height="51"></div>
<p><strong><?php echo '<br />' . $message; ?></strong></p>
<p><b><?php echo get_string("cost").": {$instance->currency} {$localisedcost}"; ?></b></p>
<div>

<form action="<?php echo "$CFG->wwwroot/local/jra/stripe/charge.php"?>" method="post">

<input type="hidden" name="cmd" value="_xclick" />
<input type="hidden" name="charset" value="utf-8" />
<input type="hidden" name="item_name" value="<?php p($coursefullname) ?>" />
<input type="hidden" name="item_number" value="<?php p($courseshortname) ?>" />
<input type="hidden" name="quantity" value="1" />
<input type="hidden" name="on0" value="<?php print_string("user") ?>" />
<input type="hidden" name="os0" value="<?php p($userfullname) ?>" />
<input type="hidden" name="custom" value="<?php echo "{$USER->id}-{$instance->cost}" ?>" />
<input type="hidden" name="currency_code" value="<?php p($instance->currency) ?>" />
<input type="hidden" name="amount" value="<?php p($cost) ?>" />
<input type="hidden" name="for_auction" value="false" />
<input type="hidden" name="no_note" value="1" />
<input type="hidden" name="no_shipping" value="1" />
<input type="hidden" name="rm" value="2" />
<input type="hidden" name="cbt" value="<?php print_string("continuetocourse") ?>" />
<input type="hidden" name="first_name" value="<?php p($userfirstname) ?>" />
<input type="hidden" name="last_name" value="<?php p($userlastname) ?>" />
<input type="hidden" name="address" value="<?php p($useraddress) ?>" />
<input type="hidden" name="city" value="<?php p($usercity) ?>" />
<input type="hidden" name="email" value="<?php p($USER->email) ?>" />
<input type="hidden" name="country" value="<?php p($USER->country) ?>" />
<script
    src="https://checkout.stripe.com/checkout.js" class="stripe-button"
    data-label="<?php echo jra_get_string(['submit','payment']) ?>"
    data-key="<?php echo $publishablekey; ?>"
    data-image=""
    data-name="<?php p($coursefullname) ?>"
    data-description="Package Cost (<?php p($instance->currency) ?><?php p($cost) ?>)"
    data-metadata="6"
    data-currency="<?php p($instance->currency) ?>"
    data-amount="<?php p($cost * 100) ?>"
    data-data-zip-code="<?php if ($validatezipcode == 0) { echo 'false'; } else { echo 'true'; } ?>"
    data-billing-address="<?php if ($billingaddress == 0) { echo 'false'; } else { echo 'true'; } ?>"
    data-locale="<?php echo current_language(); ?>"
>
</script>
</form>
</div>
