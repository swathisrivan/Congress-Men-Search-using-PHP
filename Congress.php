/************************************************/
* File Name : Congress.php                      *
* Author    : Swathi Sridhar(swathisr@usc.edu)  *
*************************************************/
<html>
    <head>
    <title>Congress Info Search</title>
    <style>
        a:visited
        {
            color:blue;
        }
    </style>
    <script type="text/javascript">
        keywordStrings = ["Keyword*", "State/Representative*", "Committee ID*", "Bill ID*", "Amendment ID*"]
        function changeKeyword(option)
        {
            document.getElementById("key").innerHTML = keywordStrings[Number(option)];
        }
        function changeChamber(option)
        {
            if(option === "senate")
            {
                document.congress.chamber_type_house.checked =  false;
            }
            else if(option === "house")
            {
                document.congress.chamber_type_senate.checked = false;
            }
        }
        function validateForm()
        {
            var err_flag    = 0;
            var err_msg     = "";
            var select_db = document.getElementById("select_db");
            var key_val   = document.getElementById("key_val");
            var check     = key_val.value.trim();
            
            if(select_db.value == "0")
            {
                err_msg += "Congress database";
                err_flag = 1;
            }
            if(!(check))
            {
                if(err_flag)
                {
                    err_msg += ", ";
                }
                err_msg += "Keyword";
                err_flag = 1;
            }
            
            if(err_flag)
            {
                alert("Please enter the following missing information: " + err_msg);
                document.getElementById("key_val").value = check;
                return false;
            }
            document.getElementById("key_val").value = check;
            return true;
        }
        function clearPage()
        {
            if(document.getElementById("first_div"))
            {
                document.getElementById("first_div").outerHTML = "";
            }
            if(document.getElementById("second_div"))
            {
                document.getElementById("second_div").outerHTML = "";
            }
            if(document.getElementById("third_div"))
            {
                document.getElementById("third_div").outerHTML = "";
            }
            document.getElementById("key_val").value = "";
            document.congress.chamber_type_house.checked =  false;
            document.congress.chamber_type_senate.checked = true;
            document.getElementById("select_db").value = "0";
            document.getElementById("key").innerHTML = "Keyword*";
        }
    </script>
    <style>
       label
        {
            display:inline-block;
            width:140px;
            text-align:center;
        }
        fieldset
        {
            border-style: groove;
            width:280px;
            margin:0px auto;
        }
    </style>
    </head>
    <body align = "center">
        <h2 style = "margin:0px auto;text-align:center">Congress Information Search</h2><br/>
        <form method ="POST" name = "congress" action='Congress.php' onsubmit = 'return validateForm();'>
        <fieldset>
            <label for="congress_db">Congress Database</label><SELECT id = "select_db" NAME="congress_db"  onchange="changeKeyword(this.value)">
                                                              <OPTION value = "0" <?php if(isset($_POST['congress_db']) && ($_POST["congress_db"] == "0")) echo 'selected';?>> Select your option </OPTION>
                                                              <OPTION value = "1" <?php if(isset($_POST['congress_db']) && ($_POST["congress_db"] == "1")) echo 'selected'; if((isset($_POST['view_details'])) && (isset($_POST["legis"]))) echo 'selected';?>> Legislators</OPTION>
                                                              <OPTION value = "2" <?php if(isset($_POST['congress_db']) && ($_POST["congress_db"] == "2")) echo 'selected';?>> Committees</OPTION>
                                                              <OPTION value = "3" <?php if(isset($_POST['congress_db']) && ($_POST["congress_db"] == "3")) echo 'selected'; if((isset($_POST['view_details'])) && (isset($_POST["bills"]))) echo 'selected';?>> Bills</OPTION>
                                                              <OPTION value = "4" <?php if(isset($_POST['congress_db']) && ($_POST["congress_db"] == "4")) echo 'selected';?>> Amendments</OPTION>
                                                              </SELECT>
            <label for="chamber_type">Chamber</label><INPUT TYPE="radio" Name="chamber_type_senate" VALUE="senate" onchange = "changeChamber(this.value)" <?php if(isset($_POST["submit"]) && isset($_POST["chamber_type_senate"])) echo 'checked'; if((isset($_POST['view_details'])) && ($_POST["chamber"] == "senate")) echo 'checked';
                                                            if(!isset($_POST["submit"]) && !isset($_POST["view_details"])) echo 'checked';?>>Senate</INPUT>
                                                     <INPUT TYPE="radio" Name="chamber_type_house" VALUE="house" onchange = "changeChamber(this.value)" <?php if(isset($_POST["submit"]) && isset($_POST["chamber_type_house"])) echo 'checked'; if((isset($_POST['view_details'])) && ($_POST["chamber"] == "house")) echo 'checked';?> >House</INPUT>
            <label id="key" for="keyword">Keyword*</label><INPUT TYPE="text" id = "key_val" Name="keyword" SIZE=15 value="<?php echo isset($_POST["keyword"]) ? $_POST["keyword"] : (isset($_POST["view_details"]) ? $_POST["legis_keyword"] : ""); ?>"></INPUT>
            <label for="buttons"></label><INPUT id = "form_submit" TYPE="submit" VALUE = "Search" NAME ="submit" style = "margin-top:5px;margin-bottom:5px"></INPUT>
                                         <INPUT id = "form_reset" TYPE="button" VALUE="clear" NAME ="reset" style = "margin-top:5px;margin-bottom:5px" onclick = 'clearPage();'></INPUT>
                                         
            <a href = "http://sunlightfoundation.com/" target = _blank style = "text-align:center;padding-left:40px">Powered by Sunlight Foundation</a>
        </fieldset>
        </form> 
        <noscript></noscript>
    </body>
    
    <?php if(isset($_POST["submit"])):?>
    <?php 
        
        //declaration of variables
        date_default_timezone_set("America/Los_Angeles");
        $arrContextOptions = array(
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
        ),
        );  
        $echo_text = "";
        $bill_st   = "";
        $states_mapping = array('alabama'=>'AL','alaska'=>'AK','arizona'=>'AZ','arkansas'=>'AR','california'=>'CA','colorado'=>'CO',
                                'connecticut'=>'CT','delaware'=>'DE','florida'=>'FL','georgia'=>'GA','hawaii'=>'HI','idaho'=>'ID',
                                'illinois'=>'IL','indiana'=>'IN','iowa'=>'IA','kansas'=>'KS','kentucky'=>'KY','louisiana'=>'LA',
                                'maine'=>'ME','maryland'=>'MD','massachusetts'=>'MA','michigan'=>'MI','minnesota'=>'MN',
                                'mississippi'=>'MS','missouri'=>'MO','montana'=>'MT','nebraska'=>'NE','nevada'=>'NV','new hampshire'=>'NH',
                                'new jersey'=>'NJ','new mexico'=>'NM','new york'=>'NY','north carolina'=>'NC','north dakota'=>'ND',
                                'ohio'=>'OH','oklahoma'=>'OK','oregon'=>'OR','pennsylvania'=>'PA','rhode island'=>'RI','south carolina'=>'SC',
                                'south dakota'=>'SD','tennessee'=>'TN','texas'=>'TX','utah'=>'UT','vermont'=>'VT','virginia'=>'VA',
                                'washington'=>'WA','west virginia'=>'WV','wisconsin'=>'WI','wyoming'=>'WY');    
    
        $link_array = array("Select", "legislators", "committees", "bills", "amendments");
        $base_url   = "https://congress.api.sunlightfoundation.com/";
        $flag       = "none";
        $legis_flag = "state";
        $names;
    
        //construction of url
        $item  = $_POST["congress_db"];
        if($link_array[intval($item)] == "legislators")
        {
            if (array_key_exists(strtolower($_POST["keyword"]), $states_mapping)) 
            {
                $url        = $base_url."legislators?state=".$states_mapping[strtolower($_POST["keyword"])];
            }
            else
            {
                $names      = explode(" ", $_POST["keyword"]); 
                if(count($names) > 1)
                {
                    $url    = $base_url."legislators?first_name=".ucwords($names[0])."&last_name=".ucwords($names[1]);
                }
                else
                {
                    $url    = $base_url."legislators?query=".rawurlencode($_POST["keyword"]);
                } 
                $legis_flag = "repr";
            }
            $flag = "legislators";
        }
        if($link_array[intval($item)] == "committees")
        {
            $url  = $base_url."committees?committee_id=".rawurlencode(strtoupper($_POST["keyword"]));
            $flag = "committees";
        }
        if($link_array[intval($item)] == "bills")
        {
            $url  = $base_url."bills?bill_id=".rawurlencode(strtolower($_POST["keyword"]));
            $flag = "bills";
        }
        if($link_array[intval($item)] == "amendments")
        {
            $url  = $base_url."amendments?amendment_id=".rawurlencode(strtolower($_POST["keyword"]));
            $flag = "amendments";
        }
        
        $url = $url."&apikey=bcde5dabc6234180953144b6300ab8d2"."&chamber=";
        isset($_POST["chamber_type_senate"]) ? $url = $url.$_POST["chamber_type_senate"] : $url = $url.$_POST["chamber_type_house"];
    
        
        //retrieving data from the congress url
        $response = file_get_contents($url, false, stream_context_create($arrContextOptions));
        $data =  json_decode($response);
    
        if($flag == "legislators")
        {
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[1];</script>";
        }
        if($flag == "committees")
        {
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[2];</script>";
        }
        if($flag == "bills")
        {
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[3];</script>";
        }
        if($flag == "amendments")
        {
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[4];</script>";
        }
        if(!sizeof($data->results))
        {
            echo "<div id = 'first_div'><h3 style = 'margin:0px auto;text-align:center'><i>The API returned zero results for the request</i></h3></div>";
        }
        else
        {
            //display of table
            $echo_text = "<div id = 'first_div'><table id = 'table_1'border = '1' style = 'border-collapse:collapse; margin:0 auto;width:750px'>";
            if($flag == "legislators")
            {
                $echo_text = $echo_text ."<tr><th style='width:260px'>Name</th><th style='width:170px'>State</th><th style ='width:130px'>Chamber</th><th style='width:170px'>Details</th></th>";
            }
            if($flag == "committees")
            {
                $echo_text = $echo_text ."<tr><th style='width:160px'>Committee ID</th><th style='width:550px'>Committee Name</th><th style='width:160px'>Chamber</th></th>";
            }
            if($flag == "bills")
            {
                $echo_text = $echo_text ."<tr><th>Bill ID</th><th>Short Title</th><th>Chamber</th><th>Details</th></tr>";   
            }
            if($flag == "amendments")
            {
                $echo_text = $echo_text ."<tr><th>Amendment ID</th><th>Amendment Type</th><th>Chamber</th><th>Introduced on</th></tr>";
            }
            foreach($data->results as $iter)
            {
                $key_val   = $_POST["keyword"];
                $echo_text = $echo_text ."<tr>";
                if($flag == "legislators")
                {   
                    if(empty($iter->first_name) && empty($iter->last_name))
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>NA</td>";
                    }
                    else
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->first_name." ".$iter->last_name."</td>";
                    }
                    if(empty($iter->state_name))
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>NA</td>";
                    }
                    else
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->state_name."</td>";
                    }
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->chamber."</td>";
                    $echo_text = $echo_text ."<td style ='padding-left:50px'><form method = 'post' action = 'Congress.php' style= 'margin:0'><INPUT TYPE='submit' VALUE='View Details' NAME ='val_submit' style ='background-color: transparent;text-decoration: underline; border: none;color: blue;cursor: pointer;' onclick = 'setLegisVal()';></INPUT>
                    <INPUT TYPE='hidden' VALUE='true' NAME = 'view_details'></INPUT>
                    <INPUT TYPE='hidden' VALUE='true' NAME = 'legis'></INPUT>
                    <INPUT TYPE='hidden' VALUE=".$iter->chamber." NAME = 'chamber'></INPUT>
                    <INPUT TYPE='hidden' VALUE=".$iter->bioguide_id." NAME = 'bioguide_id'></INPUT>
                    <INPUT TYPE='hidden' VALUE=".$legis_flag." NAME = 'legis_flag'></INPUT>
                    <INPUT TYPE='hidden' VALUE='".$key_val."' NAME = 'legis_keyword'></INPUT></form></td>";
                }
                if($flag == "committees")
                {
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->committee_id."</td>";
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->name."</td>";
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->chamber."</td>";
                }
                if($flag == "bills")
                {
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->bill_id."</td>";
                    if(empty($iter->short_title))
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>NA</td>";
                        $bill_st   = "NA";
                    }
                    else
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->short_title."</td>";
                        $bill_st   = $iter->short_title;
                    }
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->chamber."</td>";
                    $echo_text = $echo_text ."<td style ='padding-left:50px'><form method = 'post' action = 'Congress.php' style= 'margin:0'><INPUT TYPE='submit' VALUE='View Details' NAME ='val_submit' style ='background-color: transparent;text-decoration: underline; border: none;color: blue;cursor: pointer;' onclick = 'setBillsVal()';></INPUT>
                    <INPUT TYPE='hidden' VALUE='true' NAME = 'view_details'></INPUT>
                    <INPUT TYPE='hidden' VALUE='true' NAME = 'bills'></INPUT>";
                    if(!empty($iter->bill_id))
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE=".$iter->bill_id." NAME = 'bill_id'></INPUT>";
                    }
                    else
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE= 'NA' NAME = 'bill_id'></INPUT>";
                    }
                    $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='".$bill_st."' NAME = 'bill_title'></INPUT>";
                    if(!empty($iter->sponsor->title) && !empty($iter->sponsor->first_name) && !empty($iter->sponsor->last_name))
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='".$iter->sponsor->title." ". $iter->sponsor->first_name." ".$iter->sponsor->last_name."' NAME = 'sponsor'></INPUT>";
                    }
                    else
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE= 'NA' NAME = 'sponsor'></INPUT>";
                    }
                    $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='".$key_val." 'NAME = 'legis_keyword'></INPUT>";
                    $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE=".$iter->chamber." NAME = 'chamber'></INPUT>";
                    if(!empty($iter->introduced_on))
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE=".$iter->introduced_on." NAME = 'intro'></INPUT>";
                    }
                    else
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='NA' NAME = 'intro'></INPUT>";
                    }
                    if(!empty($iter->last_version->version_name) && !empty($iter->last_action_at))
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='".$iter->last_version->version_name.", ".$iter->last_action_at."' NAME = 'laction'></INPUT>";
                    }
                    else
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='NA' NAME = 'laction'></INPUT>";
                    }
                    if(!empty($iter->last_version->urls->pdf))
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE=".$iter->last_version->urls->pdf." NAME = 'bill_url'></INPUT></form></td>";
                    }
                    else
                    {
                        $echo_text = $echo_text."<INPUT TYPE='hidden' VALUE='NA' NAME = 'bill_url'></INPUT></form>";
                    }
                }
                if($flag == "amendments")
                {
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->amendment_id."</td>";
                    if(empty($iter->amendment_type))
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>NA</td>";
                    }
                    else
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->amendment_type."</td>";
                    }
                    $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->chamber."</td>";
                    if(empty($iter->introduced_on))
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>NA</td>";
                    }
                    else
                    {
                        $echo_text = $echo_text ."<td style ='padding-left:50px'>".$iter->introduced_on."</td>";
                    }
                }
                $echo_text = $echo_text ."</tr>";
            }
            $echo_text = $echo_text ."</table></div>";
            echo $echo_text;
            unset($_POST);
        }
    ?>
    <?php endif; ?>
    <?php if(isset($_POST["val_submit"])):?>
    <?php
        //declaration of variables
        date_default_timezone_set("America/Los_Angeles");
        $arrContextOptions = array(
        "ssl"=>array(
        "verify_peer"=>false,
        "verify_peer_name"=>false,
        ),
        );
        $det_txt = "";
        if(isset($_POST["legis"]))
        {
            $get_url = "https://congress.api.sunlightfoundation.com/legislators?";
            if($_POST["legis_flag"] == "state")
            {
                $get_url = $get_url."chamber=".$_POST["chamber"]."&state=".$states_mapping[$_POST["keyword"]]."&bioguide_id=".$_POST["bioguide_id"]."&apikey=bcde5dabc6234180953144b6300ab8d2";
            }
            else
            {
                $get_url = $get_url."chamber=".$_POST["chamber"]."&query=".$_POST["keyword"]."&bioguide_id=".$_POST["bioguide_id"]."&apikey=bcde5dabc6234180953144b6300ab8d2";   
            }
            
            //retrieving data from the congress url
            $detail_response = file_get_contents($get_url, false, stream_context_create($arrContextOptions));
            $details         = json_decode($detail_response);
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[1];</script>";
            foreach($details->results as $detail_data)
            {
                $img_url = "https://theunitedstates.io/images/congress/225x275/".$detail_data->bioguide_id.".jpg";
                //display of legislators table
                $det_text = $det_text."<div id = 'second_div' style='border: 1px solid black;margin:0 auto;width:800px;'><br/>";
                $det_text = $det_text."<img src='".$img_url."'style='margin:0 auto;padding-left:290px'><br/><br/>";
                $det_text = $det_text."<table id = 'legis_table' border = '0' style = 'margin:0 auto;margin-left:200px;width:570px'>";
                $det_text = $det_text."<tr><td style='width:220px'>Full Name</td><td>".$detail_data->title." ".$detail_data->first_name." ".$detail_data->last_name."</td></tr>";
                $det_text = $det_text."<tr><td style='width:220px'>Term Ends on</td><td>".$detail_data->term_end."</td></tr>";
                $det_text = $det_text."<tr><td style='width:220px'>Website</td><td><a href='".$detail_data->website."'target=_blank>".$detail_data->website."</a></td></tr>";
                $det_text = $det_text."<tr><td style='width:220px'>Office</td><td>".$detail_data->office."</td></tr>";
                if(empty($detail_data->facebook_id))
                {
                    $det_text = $det_text."<tr><td style='width:220px'>Facebook</td><td>NA</td></tr>";
                }
                else
                {
                    $det_text = $det_text."<tr><td style='width:220px'>Facebook</td><td><a href='https://www.facebook.com/".$detail_data->facebook_id."'target=_blank>".$detail_data->first_name." ".$detail_data->last_name."</td></tr>";
                }
                if(empty($detail_data->twitter_id))
                {
                    $det_text."<tr><td style='width:220px'>Twitter</td><td>NA</td></tr>";
                }
                else
                {
                    $det_text = $det_text."<tr><td style='width:220px'>Twitter</td><td><a href='https://www.twitter.com/".$detail_data->twitter_id."'target=_blank>".$detail_data->first_name." ".$detail_data->last_name."</a></td></tr>";
                }
                $det_text = $det_text."</table><br/>";
                $det_text = $det_text."</div>";
            }
        }
        else
        {
            echo "<script> document.getElementById('key').innerHTML = keywordStrings[3];</script>";
            //display of bill table
            $det_text = $det_text."<div id = 'third_div' style='border: 1px solid black;margin:0 auto;width:700px;padding-top:20px;padding-bottom:20px'>";
            $det_text = $det_text."<table id = 'bill_table' border = '0' style = 'margin:0 auto;margin-left:60px'>";
            $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>BillID</td><td>".$_POST["bill_id"]."</td></tr>";
            $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Bill Title</td><td>".$_POST["bill_title"]."</td></tr>";
            $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Sponsor</td><td>".$_POST["sponsor"]."</td></tr>";
            $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Introduced On</td><td>".$_POST["intro"]."</td></tr>";
            $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Last action with date</td><td>".$_POST["laction"]."</td></tr>";
            if($_POST["bill_title"] == "NA")
            {
                $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Bill URL</td><td><a href='".$_POST["bill_url"]."' target=_blank>".$_POST["bill_id"]."</a></td></tr>";
            }
            else
            {
                $det_text = $det_text."<tr><td style='width:300px;padding-right:40px'>Bill URL</td><td><a href='".$_POST["bill_url"]."' target=_blank>".$_POST["bill_title"]."</a></td></tr>";
            }
            $det_text = $det_text."</table>";
            $det_text = $det_text."</div>";
        }
        echo $det_text;       
    ?>
    <?php endif; ?>
</html>