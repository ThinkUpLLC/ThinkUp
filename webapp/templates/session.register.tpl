{include file="_header.tpl" title="Registration" statusbar="no"}

<div class="container_24 thinktank-canvas round-all">
  <div class="prepend_20">
    <h1>Register</h1>
  </div>
  <div class="clearfix prepend_20">
    <div class="grid_17 prefix_3 left">
      {if isset($errormsg)}
        <div class="error">
          {$errormsg}
        </div>
      {/if} 
      {if isset($successmsg)}
        <div class="success">
          {$successmsg}
        </div>
      {/if}
    </div>
  </div>
  {if !$closed}
    <form name="form1" method="post" action="register.php" class="login append_20">
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label>
            Name:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="full_name" type="text" id="full_name"{if  isset($name)} value="{$name}"{/if}>
          <small>
            <br>
            Example: Angelina Jolie
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label>
            Email:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="email" type="text" id="email"{if  isset($mail)} value="{$mail}"{/if}>
          <small>
            <br>
            Example: angie@example.com
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label>
            Password:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="pass1" type="password" id="pass1">
          <small>
            <br>
            At least 5 characters
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_6 prefix_3 right">
          <label>
            Retype password:
          </label>
        </div>
        <div class="grid_10 left">
          <input name="pass2" type="password" id="pass2">
          <small>
            <br>
          </small>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_4 prefix_5 right">
          <label>
            Country:
          </label>
        </div>
        <div class="grid_10 left">
          <select name="country" id="select8">
            <option value="Afghanistan">Afghanistan</option>
            <option value="Albania">Albania</option>
            <option value="Algeria">Algeria</option>
            <option value="Andorra">Andorra</option>
            <option value="Anguila">Anguila</option>
            <option value="Antarctica">Antarctica</option>
            <option value="Antigua and Barbuda">Antigua and Barbuda</option>
            <option value="Argentina">Argentina</option>
            <option value="Armenia ">Armenia </option>
            <option value="Aruba">Aruba</option>
            <option value="Australia">Australia</option>
            <option value="Austria">Austria</option>
            <option value="Azerbaidjan">Azerbaidjan</option>
            <option value="Bahamas">Bahamas</option>
            <option value="Bahrain">Bahrain</option>
            <option value="Bangladesh">Bangladesh</option>
            <option value="Barbados">Barbados</option>
            <option value="Belarus">Belarus</option>
            <option value="Belgium">Belgium</option>
            <option value="Belize">Belize</option>
            <option value="Bermuda">Bermuda</option>
            <option value="Bhutan">Bhutan</option>
            <option value="Bolivia">Bolivia</option>
            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
            <option value="Brazil">Brazil</option>
            <option value="Brunei">Brunei</option>
            <option value="Bulgaria">Bulgaria</option>
            <option value="Cambodia">Cambodia</option>
            <option value="Canada">Canada</option>
            <option value="Cape Verde">Cape Verde</option>
            <option value="Cayman Islands">Cayman Islands</option>
            <option value="Chile">Chile</option>
            <option value="China">China</option>
            <option value="Christmans Islands">Christmans Islands</option>
            <option value="Cocos Island">Cocos Island</option>
            <option value="Colombia">Colombia</option>
            <option value="Cook Islands">Cook Islands</option>
            <option value="Costa Rica">Costa Rica</option>
            <option value="Croatia">Croatia</option>
            <option value="Cuba">Cuba</option>
            <option value="Cyprus">Cyprus</option>
            <option value="Czech Republic">Czech Republic</option>
            <option value="Denmark">Denmark</option>
            <option value="Dominica">Dominica</option>
            <option value="Dominican Republic">Dominican Republic</option>
            <option value="Ecuador">Ecuador</option>
            <option value="Egypt">Egypt</option>
            <option value="El Salvador">El Salvador</option>
            <option value="Estonia">Estonia</option>
            <option value="Falkland Islands">Falkland Islands</option>
            <option value="Faroe Islands">Faroe Islands</option>
            <option value="Fiji">Fiji</option>
            <option value="Finland">Finland</option>
            <option value="France">France</option>
            <option value="French Guyana">French Guyana</option>
            <option value="French Polynesia">French Polynesia</option>
            <option value="Gabon">Gabon</option>
            <option value="Germany">Germany</option>
            <option value="Gibraltar">Gibraltar</option>
            <option value="Georgia">Georgia</option>
            <option value="Greece">Greece</option>
            <option value="Greenland">Greenland</option>
            <option value="Grenada">Grenada</option>
            <option value="Guadeloupe">Guadeloupe</option>
            <option value="Guatemala">Guatemala</option>
            <option value="Guinea-Bissau">Guinea-Bissau</option>
            <option value="Guinea">Guinea</option>
            <option value="Haiti">Haiti</option>
            <option value="Honduras">Honduras</option>
            <option value="Hong Kong">Hong Kong</option>
            <option value="Hungary">Hungary</option>
            <option value="Iceland">Iceland</option>
            <option value="India">India</option>
            <option value="Indonesia">Indonesia</option>
            <option value="Ireland">Ireland</option>
            <option value="Israel">Israel</option>
            <option value="Italy">Italy</option>
            <option value="Jamaica">Jamaica</option>
            <option value="Japan">Japan</option>
            <option value="Jordan">Jordan</option>
            <option value="Kazakhstan">Kazakhstan</option>
            <option value="Kenya">Kenya</option>
            <option value="Kiribati ">Kiribati </option>
            <option value="Kuwait">Kuwait</option>
            <option value="Kyrgyzstan">Kyrgyzstan</option>
            <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
            <option value="Latvia">Latvia</option>
            <option value="Lebanon">Lebanon</option>
            <option value="Liechtenstein">Liechtenstein</option>
            <option value="Lithuania">Lithuania</option>
            <option value="Luxembourg">Luxembourg</option>
            <option value="Macedonia">Macedonia</option>
            <option value="Madagascar">Madagascar</option>
            <option value="Malawi">Malawi</option>
            <option value="Malaysia ">Malaysia </option>
            <option value="Maldives">Maldives</option>
            <option value="Mali">Mali</option>
            <option value="Malta">Malta</option>
            <option value="Marocco">Marocco</option>
            <option value="Marshall Islands">Marshall Islands</option>
            <option value="Mauritania">Mauritania</option>
            <option value="Mauritius">Mauritius</option>
            <option value="Mexico">Mexico</option>
            <option value="Micronesia">Micronesia</option>
            <option value="Moldavia">Moldavia</option>
            <option value="Monaco">Monaco</option>
            <option value="Mongolia">Mongolia</option>
            <option value="Myanmar">Myanmar</option>
            <option value="Nauru">Nauru</option>
            <option value="Nepal">Nepal</option>
            <option value="Netherlands Antilles">Netherlands Antilles</option>
            <option value="Netherlands">Netherlands</option>
            <option value="New Zealand">New Zealand</option>
            <option value="Niue">Niue</option>
            <option value="North Korea">North Korea</option>
            <option value="Norway">Norway</option>
            <option value="Oman">Oman</option>
            <option value="Pakistan">Pakistan</option>
            <option value="Palau">Palau</option>
            <option value="Panama">Panama</option>
            <option value="Papua New Guinea">Papua New Guinea</option>
            <option value="Paraguay">Paraguay</option>
            <option value="Peru ">Peru </option>
            <option value="Philippines">Philippines</option>
            <option value="Poland">Poland</option>
            <option value="Portugal ">Portugal </option>
            <option value="Puerto Rico">Puerto Rico</option>
            <option value="Qatar">Qatar</option>
            <option value="Republic of Korea Reunion">Republic of Korea Reunion</option>
            <option value="Romania">Romania</option>
            <option value="Russia">Russia</option>
            <option value="Saint Helena">Saint Helena</option>
            <option value="Saint kitts and nevis">Saint kitts and nevis</option>
            <option value="Saint Lucia">Saint Lucia</option>
            <option value="Samoa">Samoa</option>
            <option value="San Marino">San Marino</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="Seychelles">Seychelles</option>
            <option value="Singapore">Singapore</option>
            <option value="Slovakia">Slovakia</option>
            <option value="Slovenia">Slovenia</option>
            <option value="Solomon Islands">Solomon Islands</option>
            <option value="South Africa">South Africa</option>
            <option value="Spain">Spain</option>
            <option value="Sri Lanka">Sri Lanka</option>
            <option value="St.Pierre and Miquelon">St.Pierre and Miquelon</option>
            <option value="St.Vincent and the Grenadines">St.Vincent and the Grenadines</option>
            <option value="Sweden">Sweden</option>
            <option value="Switzerland">Switzerland</option>
            <option value="Syria">Syria</option>
            <option value="Taiwan ">Taiwan </option>
            <option value="Tajikistan">Tajikistan</option>
            <option value="Thailand">Thailand</option>
            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
            <option value="Turkey">Turkey</option>
            <option value="Turkmenistan">Turkmenistan</option>
            <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
            <option value="Ukraine">Ukraine</option>
            <option value="UAE">UAE</option>
            <option value="UK">UK</option>
            <option value="USA" selected="true">USA</option>
            <option value="Uruguay">Uruguay</option>
            <option value="Uzbekistan">Uzbekistan</option>
            <option value="Vanuatu">Vanuatu</option>
            <option value="Vatican City">Vatican City</option>
            <option value="Vietnam">Vietnam</option>
            <option value="Virgin Islands (GB)">Virgin Islands (GB)</option>
            <option value="Virgin Islands (U.S.) ">Virgin Islands (U.S.) </option>
            <option value="Wallis and Futuna Islands">Wallis and Futuna Islands</option>
            <option value="Yemen">Yemen</option>
            <option value="Yugoslavia">Yugoslavia</option>
          </select>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_6 prefix_3 right">
          <label>
            Prove you're human:
          </label>
        </div>
        <div class="grid_10 left">
          <div class="captcha">
            {$captcha}
          </div>
        </div>
      </div>
      <div class="clearfix">
        <div class="grid_10 prefix_9 left">
          <input type="submit" name="Submit" id="login-save" class="tt-button ui-state-default ui-priority-secondary ui-corner-all" value="Register">
        </div>
      </div>
    </form>
  {/if}
  <div class="center prepend_20 append_20">
    <a href="login.php">Log In</a> |
    <a href="forgot.php">Forgot password</a>
  </div>
</div>

{include file="_footer.tpl" stats="no"}