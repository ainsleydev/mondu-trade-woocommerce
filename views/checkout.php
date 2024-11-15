<?php if ( ! is_user_logged_in() ): ?>
	<div class="woocommerce-error" style="margin-bottom: 0; margin-top: 10px;">
		<a href="<?php echo get_permalink( wc_get_page_id( 'myaccount' ) ); ?>">Log in</a> to register to Pay using a
		Mondu Trade Account.
	</div>
<?php else: ?>
	<fieldset id="wc-a-dev-trade-account" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
		<div class="form-row form-row-wide">
			<p>You're not currently registered for a Trade Account, compelete the form below to be redirected to
				Mondu</p>
		</div>
		<div class="form-row form-row-wide">
			<!-- Address -->
			<div class="form-row form-row-wide">
				<label for="a-dev-address-line1">Address Line 1 <span class="required">*</span></label>
				<input id="a-dev-address-line1" name="address_line1" type="text" autocomplete="address-line1"
					   placeholder="Alexanderstr. 36">
			</div>
			<!-- City -->
			<div class="form-row form-row-wide">
				<label for="a-dev-city">City <span class="required">*</span></label>
				<input id="a-dev-city" name="city" type="text" autocomplete="address-level2" placeholder="London">
			</div>
			<!-- Zip Code -->
			<div class="form-row form-row-wide">
				<label for="a-dev-zip-code">Post Code <span class="required">*</span></label>
				<input id="a-dev-zip-code" name="zip_code" type="text" autocomplete="postal-code" placeholder="NW1">
			</div>
			<!-- Country Code -->
			<div class="form-row form-row-wide">
				<label for="a-dev-country-code">Country Code <span class="required">*</span></label>
				<select id="a-dev-country-code" class="country_to_state country_select" name="country_code" required>
					<option value="" disabled selected>Select your country</option>
					<option value="AF">Afghanistan (AF)</option>
					<option value="AL">Albania (AL)</option>
					<option value="AQ">Antarctica (AQ)</option>
					<option value="DZ">Algeria (DZ)</option>
					<option value="AS">American Samoa (AS)</option>
					<option value="AD">Andorra (AD)</option>
					<option value="AO">Angola (AO)</option>
					<option value="AG">Antigua and Barbuda (AG)</option>
					<option value="AZ">Azerbaijan (AZ)</option>
					<option value="AR">Argentina (AR)</option>
					<option value="AU">Australia (AU)</option>
					<option value="AT">Austria (AT)</option>
					<option value="BS">Bahamas (BS)</option>
					<option value="BH">Bahrain (BH)</option>
					<option value="BD">Bangladesh (BD)</option>
					<option value="AM">Armenia (AM)</option>
					<option value="BB">Barbados (BB)</option>
					<option value="BE">Belgium (BE)</option>
					<option value="BM">Bermuda (BM)</option>
					<option value="BT">Bhutan (BT)</option>
					<option value="BO">Bolivia (BO)</option>
					<option value="BA">Bosnia and Herzegovina (BA)</option>
					<option value="BW">Botswana (BW)</option>
					<option value="BV">Bouvet Island (BV)</option>
					<option value="BR">Brazil (BR)</option>
					<option value="BZ">Belize (BZ)</option>
					<option value="IO">British Indian Ocean Territory (IO)</option>
					<option value="SB">Solomon Islands (SB)</option>
					<option value="VG">Virgin Islands, British (VG)</option>
					<option value="BN">Brunei Darussalam (BN)</option>
					<option value="BG">Bulgaria (BG)</option>
					<option value="MM">Myanmar (MM)</option>
					<option value="BI">Burundi (BI)</option>
					<option value="BY">Belarus (BY)</option>
					<option value="KH">Cambodia (KH)</option>
					<option value="CM">Cameroon (CM)</option>
					<option value="CA">Canada (CA)</option>
					<option value="CV">Cape Verde (CV)</option>
					<option value="KY">Cayman Islands (KY)</option>
					<option value="CF">Central African Republic (CF)</option>
					<option value="LK">Sri Lanka (LK)</option>
					<option value="TD">Chad (TD)</option>
					<option value="CL">Chile (CL)</option>
					<option value="CN">China (CN)</option>
					<option value="TW">Taiwan (TW)</option>
					<option value="CX">Christmas Island (CX)</option>
					<option value="CC">Cocos (Keeling) Islands (CC)</option>
					<option value="CO">Colombia (CO)</option>
					<option value="KM">Comoros (KM)</option>
					<option value="YT">Mayotte (YT)</option>
					<option value="CG">Congo (CG)</option>
					<option value="CD">Congo, the Democratic Republic of the (CD)</option>
					<option value="CK">Cook Islands (CK)</option>
					<option value="CR">Costa Rica (CR)</option>
					<option value="HR">Croatia (HR)</option>
					<option value="CU">Cuba (CU)</option>
					<option value="CY">Cyprus (CY)</option>
					<option value="CZ">Czech Republic (CZ)</option>
					<option value="BJ">Benin (BJ)</option>
					<option value="DK">Denmark (DK)</option>
					<option value="DM">Dominica (DM)</option>
					<option value="DO">Dominican Republic (DO)</option>
					<option value="EC">Ecuador (EC)</option>
					<option value="SV">El Salvador (SV)</option>
					<option value="GQ">Equatorial Guinea (GQ)</option>
					<option value="ET">Ethiopia (ET)</option>
					<option value="ER">Eritrea (ER)</option>
					<option value="EE">Estonia (EE)</option>
					<option value="FO">Faroe Islands (FO)</option>
					<option value="FK">Falkland Islands (Malvinas) (FK)</option>
					<option value="GS">South Georgia and the South Sandwich Islands (GS)</option>
					<option value="FJ">Fiji (FJ)</option>
					<option value="FI">Finland (FI)</option>
					<option value="AX">Åland Islands (AX)</option>
					<option value="FR">France (FR)</option>
					<option value="GF">French Guiana (GF)</option>
					<option value="PF">French Polynesia (PF)</option>
					<option value="TF">French Southern Territories (TF)</option>
					<option value="DJ">Djibouti (DJ)</option>
					<option value="GA">Gabon (GA)</option>
					<option value="GE">Georgia (GE)</option>
					<option value="GM">Gambia (GM)</option>
					<option value="PS">Palestinian Territory (PS)</option>
					<option value="DE">Germany (DE)</option>
					<option value="GH">Ghana (GH)</option>
					<option value="GI">Gibraltar (GI)</option>
					<option value="KI">Kiribati (KI)</option>
					<option value="GR">Greece (GR)</option>
					<option value="GL">Greenland (GL)</option>
					<option value="GD">Grenada (GD)</option>
					<option value="GP">Guadeloupe (GP)</option>
					<option value="GU">Guam (GU)</option>
					<option value="GT">Guatemala (GT)</option>
					<option value="GN">Guinea (GN)</option>
					<option value="GY">Guyana (GY)</option>
					<option value="HT">Haiti (HT)</option>
					<option value="HM">Heard Island and McDonald Islands (HM)</option>
					<option value="VA">Holy See (Vatican City State) (VA)</option>
					<option value="HN">Honduras (HN)</option>
					<option value="HK">Hong Kong (HK)</option>
					<option value="HU">Hungary (HU)</option>
					<option value="IS">Iceland (IS)</option>
					<option value="IN">India (IN)</option>
					<option value="ID">Indonesia (ID)</option>
					<option value="IR">Iran (IR)</option>
					<option value="IQ">Iraq (IQ)</option>
					<option value="IE">Ireland (IE)</option>
					<option value="IL">Israel (IL)</option>
					<option value="IT">Italy (IT)</option>
					<option value="CI">Ivory Coast (CI)</option>
					<option value="JM">Jamaica (JM)</option>
					<option value="JP">Japan (JP)</option>
					<option value="KZ">Kazakhstan (KZ)</option>
					<option value="JO">Jordan (JO)</option>
					<option value="KE">Kenya (KE)</option>
					<option value="KP">Korea, North (KP)</option>
					<option value="KR">Korea, South (KR)</option>
					<option value="KW">Kuwait (KW)</option>
					<option value="KG">Kyrgyzstan (KG)</option>
					<option value="LA">Laos (LA)</option>
					<option value="LB">Lebanon (LB)</option>
					<option value="LS">Lesotho (LS)</option>
					<option value="LV">Latvia (LV)</option>
					<option value="LR">Liberia (LR)</option>
					<option value="LY">Libya (LY)</option>
					<option value="LI">Liechtenstein (LI)</option>
					<option value="LT">Lithuania (LT)</option>
					<option value="LU">Luxembourg (LU)</option>
					<option value="MO">Macau (MO)</option>
					<option value="MG">Madagascar (MG)</option>
					<option value="MW">Malawi (MW)</option>
					<option value="MY">Malaysia (MY)</option>
					<option value="MV">Maldives (MV)</option>
					<option value="ML">Mali (ML)</option>
					<option value="MT">Malta (MT)</option>
					<option value="MQ">Martinique (MQ)</option>
					<option value="MR">Mauritania (MR)</option>
					<option value="MU">Mauritius (MU)</option>
					<option value="MX">Mexico (MX)</option>
					<option value="MC">Monaco (MC)</option>
					<option value="MN">Mongolia (MN)</option>
					<option value="MD">Moldova (MD)</option>
					<option value="ME">Montenegro (ME)</option>
					<option value="MS">Montserrat (MS)</option>
					<option value="MA">Morocco (MA)</option>
					<option value="MZ">Mozambique (MZ)</option>
					<option value="OM">Oman (OM)</option>
					<option value="NA">Namibia (NA)</option>
					<option value="NR">Nauru (NR)</option>
					<option value="NP">Nepal (NP)</option>
					<option value="NL">Netherlands (NL)</option>
					<option value="CW">Curaçao (CW)</option>
					<option value="AW">Aruba (AW)</option>
					<option value="SX">Sint Maarten (SX)</option>
					<option value="BQ">Bonaire (BQ)</option>
					<option value="NC">New Caledonia (NC)</option>
					<option value="VU">Vanuatu (VU)</option>
					<option value="NZ">New Zealand (NZ)</option>
					<option value="NI">Nicaragua (NI)</option>
					<option value="NE">Niger (NE)</option>
					<option value="NG">Nigeria (NG)</option>
					<option value="NU">Niue (NU)</option>
					<option value="NF">Norfolk Island (NF)</option>
					<option value="NO">Norway (NO)</option>
					<option value="MP">Northern Mariana Islands (MP)</option>
					<option value="UM">United States Minor Outlying Islands (UM)</option>
					<option value="FM">Micronesia (FM)</option>
					<option value="MH">Marshall Islands (MH)</option>
					<option value="PW">Palau (PW)</option>
					<option value="PK">Pakistan (PK)</option>
					<option value="PA">Panama (PA)</option>
					<option value="PG">Papua New Guinea (PG)</option>
					<option value="PY">Paraguay (PY)</option>
					<option value="PE">Peru (PE)</option>
					<option value="PH">Philippines (PH)</option>
					<option value="PN">Pitcairn (PN)</option>
					<option value="PL">Poland (PL)</option>
					<option value="PT">Portugal (PT)</option>
					<option value="GW">Guinea-Bissau (GW)</option>
					<option value="TL">Timor-Leste (TL)</option>
					<option value="PR">Puerto Rico (PR)</option>
					<option value="QA">Qatar (QA)</option>
					<option value="RE">Réunion (RE)</option>
					<option value="RO">Romania (RO)</option>
					<option value="RU">Russia (RU)</option>
					<option value="RW">Rwanda (RW)</option>
					<option value="BL">Saint Barthélemy (BL)</option>
					<option value="SH">Saint Helena (SH)</option>
					<option value="KN">Saint Kitts and Nevis (KN)</option>
					<option value="AI">Anguilla (AI)</option>
					<option value="LC">Saint Lucia (LC)</option>
					<option value="MF">Saint Martin (MF)</option>
					<option value="PM">Saint Pierre and Miquelon (PM)</option>
					<option value="VC">Saint Vincent and the Grenadines (VC)</option>
					<option value="SM">San Marino (SM)</option>
					<option value="ST">Sao Tome and Principe (ST)</option>
					<option value="SA">Saudi Arabia (SA)</option>
					<option value="SN">Senegal (SN)</option>
					<option value="RS">Serbia (RS)</option>
					<option value="SC">Seychelles (SC)</option>
					<option value="SL">Sierra Leone (SL)</option>
					<option value="SG">Singapore (SG)</option>
					<option value="SK">Slovakia (SK)</option>
					<option value="VN">Vietnam (VN)</option>
					<option value="SI">Slovenia (SI)</option>
					<option value="SO">Somalia (SO)</option>
					<option value="ZA">South Africa (ZA)</option>
					<option value="ZW">Zimbabwe (ZW)</option>
					<option value="ES">Spain (ES)</option>
					<option value="SS">South Sudan (SS)</option>
					<option value="SD">Sudan (SD)</option>
					<option value="EH">Western Sahara (EH)</option>
					<option value="SR">Suriname (SR)</option>
					<option value="SJ">Svalbard and Jan Mayen (SJ)</option>
					<option value="SZ">Eswatini (SZ)</option>
					<option value="SE">Sweden (SE)</option>
					<option value="CH">Switzerland (CH)</option>
					<option value="SY">Syria (SY)</option>
					<option value="TJ">Tajikistan (TJ)</option>
					<option value="TH">Thailand (TH)</option>
					<option value="TG">Togo (TG)</option>
					<option value="TK">Tokelau (TK)</option>
					<option value="TO">Tonga (TO)</option>
					<option value="TT">Trinidad and Tobago (TT)</option>
					<option value="AE">United Arab Emirates (AE)</option>
					<option value="TN">Tunisia (TN)</option>
					<option value="TR">Turkey (TR)</option>
					<option value="TM">Turkmenistan (TM)</option>
					<option value="TC">Turks and Caicos Islands (TC)</option>
					<option value="TV">Tuvalu (TV)</option>
					<option value="UG">Uganda (UG)</option>
					<option value="UA">Ukraine (UA)</option>
					<option value="MK">North Macedonia (MK)</option>
					<option value="EG">Egypt (EG)</option>
					<option value="GB">United Kingdom (GB)</option>
					<option value="GG">Guernsey (GG)</option>
					<option value="JE">Jersey (JE)</option>
					<option value="IM">Isle of Man (IM)</option>
					<option value="TZ">Tanzania (TZ)</option>
					<option value="US">United States (US)</option>
					<option value="VI">Virgin Islands (VI)</option>
					<option value="BF">Burkina Faso (BF)</option>
					<option value="UY">Uruguay (UY)</option>
					<option value="UZ">Uzbekistan (UZ)</option>
					<option value="VE">Venezuela (VE)</option>
					<option value="WF">Wallis and Futuna (WF)</option>
					<option value="WS">Samoa (WS)</option>
					<option value="YE">Yemen (YE)</option>
					<option value="ZM">Zambia (ZM)</option>
				</select>
			</div>
			<!-- Clear -->
			<div class="clear"></div>
			<!-- Submit Button, Note: Have to use a div here to prevent checkout submission -->
			<div class="form-row form-row-wide">
				<div id="a-dev-submit-trade-application" class="button alt"
					 onclick="submitTradeAccountApplication(event)">Apply Now
				</div>
			</div>
		</div>
	</fieldset>
<?php endif; ?>
