<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--
Copyright (c) 2009 Frank Font - Room4me.com Software LLC

Email: consulting@room4me.com

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
-->
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="css/basic.css" type="text/css" />
        <title>Raw Load Tester v1.0</title>

        <SCRIPT language="JavaScript">
        function submitform(sActionCode, nDelaySeconds)
        {
            nDelayMS = nDelaySeconds * 1000;
            //alert("submitformNoDelay('"+sActionCode+"',"+nDelayMS+")");
            setTimeout("submitformNoDelay('"+sActionCode+"')",nDelayMS);
        }
        function submitformNoDelay(sActionCode)
        {
            //alert('about to submit!');
            document.form1.txtActionCode.value = sActionCode;
            document.form1.submit();
        }
        </SCRIPT>
    </head>
    <?php
        $RawLoadTesterVersionInfo="v1.0";
        $sUserID = $_SERVER["REMOTE_USER"]."@".$_SERVER["REMOTE_ADDR"]; //Get some information about the caller.
        $sActionCode = $_REQUEST["txtActionCode"];
        $nStartTime = $_REQUEST["txtStartTime"];
        $nTimeSpread = $_REQUEST["txtTimeSpread"]+0;
        $sAvoidCache = $_REQUEST["lstAvoidCache"];
        if($sAvoidCache == 'Yes')
        {
            $nURLParamVal = $_REQUEST["txtURLParamVal"]+1;
        } else {
            $nURLParamVal = -1; //No param.
        }
        $nPause = $_REQUEST["txtPause"];
        if($nPause == '')
        {
            $nPause = 0;
        }
        $nPrevTime = $_REQUEST["txtPrevTime"];
        $nAvgTimeInterval = $_REQUEST["txtAvgTimeInterval"];
        $nMaxTimeInterval = $_REQUEST["txtMaxTimeInterval"];
        $nMinTimeInterval = $_REQUEST["txtMinTimeInterval"];
        if($nMinTimeInterval !== '' && $nMinTimeInterval !== null)
        {
            $nMinTimeInterval = $_REQUEST["txtMinTimeInterval"];
        } else {
            $nMinTimeInterval = 9999;   //initialize
        }
        $nCurrentTime = time();
        if($sActionCode == 'STARTRUN')
        {
            //Initialize.
            $nStartTime = $nCurrentTime;
            $nTimeInterval = 0;
            $nMaxTimeInterval = 0;
            $nMinTimeInterval = 9999;
        } else {
            if($nPrevTime != '')
            {
                //Actual amount of time it took to load the page.
                $nTimeInterval = $nCurrentTime - $nPrevTime - $nPause;
            }
        }
        if($nAvgTimeInterval != '')
        {
            $nAvgTimeInterval = ($nAvgTimeInterval + $nTimeInterval) / 2;
        } else {
            $nAvgTimeInterval = $nTimeInterval;
        }
        if($nTimeInterval > $nMaxTimeInterval)
        {
            $nMaxTimeInterval = $nTimeInterval;
        }
        if($nTimeInterval > 0 && $nTimeInterval < $nMinTimeInterval)
        {
            $nMinTimeInterval = $nTimeInterval;
        }
        $nTimeRemaining = $nTimeSpread;    //Default.
        if($sActionCode != '')
        {
            $sPageColor='red';
            $nTestIteration = $_REQUEST["txtTestIteration"]+0;
            $sTestURL = $_REQUEST["txtTestURL"];
            $sRealTestURL = $_REQUEST["txtRealTestURL"];
            $nTestCounter = $_REQUEST["txtTestCounter"]+0;
            $nTimeSpread = $_REQUEST["txtTimeSpread"]+0;
            if($sActionCode == "STARTRUN")
            {
                if($nURLParamVal > -1)
                {
                    if(strpos($sTestURL,'?')===false)
                    {
                        //URL does not already have params.
                        $sRealTestURL = $sTestURL."?CacheAvoidVal=";
                    } else {
                        //URL already has params, add one more.
                        $sRealTestURL = $sTestURL."&CacheAvoidVal=";
                    }
                } else {
                    $sRealTestURL = $sTestURL;
                }
                $nTestIteration = $nTestCounter;  //We will count down.
                $nAvgTimeInterval = ''; //Initialize it.
                $nPrevTime = $nCurrentTime; //Initialize it.
                $nTimeInterval = 0;
                $nMaxTimeInterval = 0;
                $nMinTimeInterval = 9999;
                //Write to the PHP log.
                error_log("RAWLOADTEST$RawLoadTesterVersionInfo([$sUserID][$sActionCode][URL=$sTestURL]):$nTestIteration of $nTestCounter with time $nTimeInterval");
                $sActionCode = "TESTING";
            } else {
                $nTestIteration--;
                if($nTestIteration < 1)
                {
                    $sActionCode = "DONE";
                    $sPageColor='yellow';
                    $nURLParamVal = 0;  //Reset this value now.
                    //Write to the PHP log.
                    error_log("RAWLOADTEST$RawLoadTesterVersionInfo([$sUserID][$sActionCode][URL=$sTestURL]):[AvgInterval=$nAvgTimeInterval][MinInterval=$nMinTimeInterval][MaxInterval=$nMaxTimeInterval]");
                } else {
                    //TESTING is in progress.
                    if($nTimeSpread > 0)
                    {
                        //Estimate an appropriate delay.
                        $nTimeSpreadSeconds = $nTimeSpread*3600;
                        $nTimeSoFar = $nCurrentTime - $nStartTime;
                        $nTimeRemaining = $nTimeSpreadSeconds - $nTimeSoFar;
                        $nTimeAvailableForIteration = $nTimeRemaining / $nTestIteration;
                        if($nURLParamVal>-1)
                        {
                            $nURLParamVal++;
                        }

                        //echo "<p>[[[$nTimeSpreadSeconds $nTimeSoFar $nTimeRemaining $nTimeAvailableForIteration<br>]]]<p>";

                        if($nTimeAvailableForIteration < $nAvgTimeInterval)
                        {
                            $nPause = 0;    //No delay.
                        } else {
                            $nPause = $nTimeAvailableForIteration - $nAvgTimeInterval;
                        }
                    } else {
                        //No delay.
                        $nPause = 0;
                    }
                    //Write to the PHP log.
                    error_log("RAWLOADTEST$RawLoadTesterVersionInfo([$sUserID][$sActionCode][URL=$sTestURL])[Delay=$nPause]:[AvgInterval=$nAvgTimeInterval][MinInterval=$nMinTimeInterval][MaxInterval=$nMaxTimeInterval]");
                }
            }
        } else {
            $sPageColor='yellow';
            //$sTestURL = "http://localhost/RawLoadTester/TESTING.html";
            $sTestURL = "http://".$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"];
            $nTestCounter = 0;
            $nTimeSpread = 0;
            $nURLParamVal = 0;
        }
    ?>
    <body bgcolor='<?php echo $sPageColor; ?>'>
        <div id="main">

        <form name="form1" id="form1" method="post" action="RawLoadTester.php">
        <input readonly=readonly type="hidden" id="txtActionCode" name="txtActionCode" value="<?php echo $sActionCode; ?>" >
        <input readonly=readonly type="hidden" id="txtStartTime" name="txtStartTime" value="<?php echo $nStartTime; ?>">
        <input readonly=readonly type="hidden" id="txtPause" name="txtPause" value="<?php echo $nPause; ?>">
        <input readonly=readonly type="hidden" id="txtTestIteration" name="txtTestIteration" value="<?php echo $nTestIteration; ?>">
        <input readonly=readonly type="hidden" id="txtPrevTime" name="txtPrevTime" value="<?php echo $nCurrentTime; ?>">
        <input readonly=readonly type="hidden" id="txtAvgTimeInterval" name="txtAvgTimeInterval" value="<?php echo $nAvgTimeInterval; ?>">
        <input readonly=readonly type="hidden" id="txtMaxTimeInterval" name="txtMaxTimeInterval" value="<?php echo $nMaxTimeInterval; ?>">
        <input readonly=readonly type="hidden" id="txtMinTimeInterval" name="txtMinTimeInterval" value="<?php echo $nMinTimeInterval; ?>">
        <input readonly=readonly type="hidden" id="txtURLParamVal" name="txtURLParamVal" value="<?php echo $nURLParamVal; ?>">
        <input readonly=readonly type="hidden" id="txtRealTestURL" name="txtRealTestURL" value="<?php echo $sRealTestURL; ?>" >

        <?php IF($nTestIteration>0): ?>

            <h1>Raw Load Tester v1.0 ... Running</h1>

            <?php IF($nPause>0): ?>
            <p>
            <b>NOTE:</b> There will be a <?php echo $nPause; ?> second delay between iterations because time spread setting was not zero!
            <br>(Trying to spread all the remaining iterations over a <?php echo $nTimeRemaining; ?> second time span.)
            </p>
            <?php ENDIF; ?>

            <?php
            if($nURLParamVal > 0)
            {
                //Include the cache avoidance value (try to make unique for each call).
                $sGotoURL=$sRealTestURL.$sUserID.'-'.$nURLParamVal.'-'.$nCurrentTime;
            } else {
                //No cache avoidance in the URL.
                $sGotoURL=$sRealTestURL;
            }
            //echo "<br>[[[[$sGotoURL]][[$sRealTestURL]][[$sTestURL]]]]<br>";
            ?>

            <label for='txtTestURL'>Test URL</label>
            <input readonly=readonly type="text" id="txtTestURL" name="txtTestURL" value="<?php echo $sTestURL; ?>" size="100">
            <br>
            <label for='txtTestCounter'>Test Counter</label>
            <input readonly=readonly type="text" id="txtTestCounter" name="txtTestCounter" value="<?php echo $nTestCounter; ?>" size="5">
            <br>
            <label for='txtTimeSpread'>Time Spread (hours)</label>
            <input readonly=readonly type="text" id="txtTimeSpread" name="txtTimeSpread" value="<?php echo $nTimeSpread; ?>" size="5">
            <br>
            <label for='lstAvoidCache'>Avoid Cache</label>
            <input readonly=readonly type="text" id="lstAvoidCache" name="lstAvoidCache" value="<?php echo $sAvoidCache; ?>" size="5">

            <p><b><?php echo $nTestIteration; ?></b> iterations remaining of <?php echo $nTestCounter ?> ...</p>

            <p align="left">
            <iframe id="myframe" name="myframe" onload="submitform('TESTING',<?php echo $nPause; ?>)" src="<?php echo $sGotoURL; ?>"
            width="600" height="200" frameborder="2"></iframe>
            </p>

        <?php ELSE: ?>

            <img class="floatRight" src="images/R4MElephantA_100.gif" alt="logo">
            <h1>Raw Load Tester v1.0 called from <?php echo $sUserID; ?></h1>

            <?php IF($sActionCode=="DONE"): ?>
                <div id="done">
                <h2>Test of <?php echo $nTestCounter; ?> iterations is complete!</h2>
                <ol>
                    <li>
                       Called <b><?php echo $sTestURL; ?></b> <?php echo $nTestCounter; ?> times
                    </li>
                    <li>
                       Average interval between loads was <?php echo $nAvgTimeInterval; ?> seconds
                    </li>
                    <li>
                        Minimum interval between loads was <?php echo $nMinTimeInterval; ?> seconds
                    </li>
                    <li>
                        Maximum interval between loads was <?php echo $nMaxTimeInterval; ?> seconds
                    </li>
                </ol>
                </div>
            <?php ENDIF; ?>

            <p><b>
            Use this page with caution, it will call the "Test URL" in rapid succession for "Test Counter" times
            </b></p>
            <p><i>This script DOES NOT check for page load errors.  If that matters to you, consider creating a custom target page on the server.</i></p>
            <label for='txtTestURL'>Test URL</label>
            <input type="text" id="txtTestURL" name="txtTestURL" value="<?php echo $sTestURL; ?>" size="100">
            <br>
            <label for='txtTestCounter'>Test Counter</label>
            <input type="text" id="txtTestCounter" name="txtTestCounter" value="<?php echo $nTestCounter; ?>" size="5">
            <i><small>Script will call the Test URL this many times.</small></i>
            <br>
            <label for='txtTimeSpread'>Time Spread (hours)</label>
            <input type="text" id="txtTimeSpread" name="txtTimeSpread" value="<?php echo $nTimeSpread; ?>" size="5">
            <i><small>Script will try to issue the Test spread out over this amount of time.  Leave as 0 if you want to test without pauses.</small></i>
            <br>
            <label for='lstAvoidCache'>Avoid Cache</label>
            <select id="lstAvoidCache" name="lstAvoidCache">
                <option value="Yes">Yes</option>
                <option value="No">No</option>
            </select>
            <i><small>Script will append a changing parameter to the Test URL to avoid the server cache.</small></i>
            <br>
            <br>
            <center>
            <input type=button onclick="submitformNoDelay('STARTRUN')" value="Start the Test" />
            </center>

            <hr>
            <center>
                <small><a href="instructions.html">Instructions</a></small>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <small><a href="license.html">Open Source License</a></small>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <small><a href="http://www.room4me.com">Room4me.com Software LLC</a></small>
            </center>
        <?php ENDIF; ?>
        </form>
        </div>
    </body>
</html>
