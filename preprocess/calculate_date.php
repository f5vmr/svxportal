<?php
set_time_limit(800);
include '../config.php';
$link->set_charset("utf8");
include '../function.php';
define_settings();
set_language();

$link-> options(MYSQLI_OPT_CONNECT_TIMEOUT, 86400);

function secondsToDHMS($seconds)
{
    $s = (int) $seconds;
    if ($s > 0)
        return sprintf('%d:%02d:%02d:%02d', $s / 86400, $s / 3600 % 24, $s / 60 % 60, $s % 60);
    else
        return "0:00:00:00";
}
function detect_empty()
{
    global $link;
    $nummber = $link->query("SELECT COUNT(*) as c FROM traffic_day_statistics ")->fetch_object()->c; 
    
    
    return $nummber;
    
}
function detect_first_date()
{
    global $link;
    $date = $link->query("SELECT Time as c FROM `ReflectorNodeLog` WHERE `Type` = 1 AND `Active` = 0 ORDER BY `ReflectorNodeLog`.`Time` ASC LIMIT 1 ")->fetch_object()->c;
    
    return date("Y-m-d",strtotime($date));
    
}
function detect_working_date()
{
    global $link;
    $date = $link->query("SELECT Timestamp as c FROM `traffic_day_statistics` ORDER BY `Timestamp` DESC LIMIT 1 ")->fetch_object()->c;
    
    return date("Y-m-d",strtotime($date));
    
}

function calculate_day ($day)
{
    global $host;
    global $user;
    global $password;
    global $db;
    $link =  mysqli_connect($host, $user, $password , $db);
    
    
    $day = $link->real_escape_string($day);
    
    // $time_string ="`Time` BETWEEN '$day $timel:00:00.000000' AND '$day $timel:59:59.000000'";
    $time_string = "`Time` BETWEEN '$day 00:00:00.000000' AND '$day 23:59:59.000000'";
    
    // $sql_active ="SELECT sum(UNIX_TIMESTAMP(`Time`)), `Talkgroup` FROM ReflectorNodeLog WHERE `Type` = '1' $station_query AND `Active` ='1' AND $time_string group by `Talkgroup`";
    //$station_string = " `Callsign` = 'SIP1' AND";
    
    
    
    $sql_nonactive = "SELECT UNIX_TIMESTAMP(Time), Callsign, Talktime, `Talkgroup` FROM ReflectorNodeLog WHERE  `Type` = 1  AND `Active` = 0 AND $time_string   ";
    
    // echo $sql_nonactive;
    
    $sqla = $link->query($sql_nonactive);
    $data = array();
    
    // move data from SQL to RAM
    while ($row = $sqla->fetch_assoc()) {
        $data[$row['Callsign']][] = $row;
    }
    
    
    
    // timesum sortation
    
    for ($timel = 0; $timel < 24; $timel ++) {
        // calculate timestamp border
        $filterdate = strtotime($day);
        $low_time = $filterdate + ($timel * 3600);
        $high_time = $filterdate + ($timel * 3600) + (59 * 60) + 59;
        
        // loop through remove row if match is found
        
        foreach ($data as $key => $station) {
            
            $timesum = array();
            $timesums = array();
            $timesums[$key] = array();
            $timesums[$key][$timel] = 0;
            $timesums[$key][$timel] = array();
            $numberof_qso[$key] = 0;
            $numberof_qso[$key] = array();
            
            foreach ($data[$key] as & $row)
            {
                
                if ($row['UNIX_TIMESTAMP(Time)'] >= $low_time && $row['UNIX_TIMESTAMP(Time)'] <= $high_time) {
                    
                    @$tables[$key][$row['Talkgroup']][$timel]["_T"] += (float) $row["Talktime"];
                    @$tables[$key][$row['Talkgroup']][$timel]["_N"] ++;
                    unset($row);
                } else {
                    @$tables[$key][$row['Talkgroup']][$timel]["_N"] += (float) 0;
                    @$tables[$key][$row['Talkgroup']][$timel]["_T"] += (float) $timesums[$key][$row['Talkgroup']][$timel];
                }
                
               @$tables[$key][$row['Talkgroup']][$timel]["_X2"] += @(float) pow($tables[$key][$row['Talkgroup']][$timel]["_T"], 2);
            }
        }
    }
    // station look loop
    foreach ($tables as $key => $tg)
    {
        $mysql_query ="";
        
        
        
        
        
        
        // talkgroup look loop
        foreach ($tg as $currenttg => $current_time)
        {
            
            $columns = "";
            $values = "";
            $total_N =0;
            $total_T =0;
            $total_X2 =0;
            
            
            // Tiime look_loop
            foreach ($current_time as $timel => $value)
            {
                
                
                
                if ($timel < 10) {
                    $extra = '0';
                } else {
                    $extra = '';
                }
                
                $columns .= "," . $extra . $timel . "_N";
                $columns .= "," . $extra . $timel . "_T";
                $columns .= "," . $extra . $timel . "_X2";
                
                @$values .= ",".(float)$value["_N"].",".(float)$value["_T"].",".(float)pow($value["_T"],2)."";
                
                @$total_N += $value["_N"];
                @$total_T += $value["_T"];
                @$total_X2 += pow($value["_T"],2);
                
            }
            if(is_numeric($currenttg))
            {
                $sql_other_col ="Node,TG,Year,Month,Day,Timestamp";
                $standard_data = '\''.$key.'\''.",".$currenttg.",".date("Y",strtotime($day)).",".date("m",strtotime($day)).",".date("d",strtotime($day)).",'".date("Y-m-d H:i:s",strtotime($day))."'";
                $total_str =  ",Total_N,Total_X2, Total_T";
                $total_val =",".$total_N.",".$total_X2.",".$total_T;
                // echo $sql_other_col.$columns . "<BR />";
                // echo  $standard_data. $values . "<BR />";
                
                //echo $key." ".$currenttg." ".$total_T.PHP_EOL;
                
                $mysql_query .="INSERT INTO `traffic_day_statistics`(".$sql_other_col.$columns.$total_str.") VALUES (". $standard_data. $values.$total_val .");";
                //echo $mysql_query;
                
                //echo $sql."<br />";
                
                
            }
            
            
            
            
            
        }
        
        
        
        
         $link-> multi_query($mysql_query);
         
         do {
         
         } while ($link->next_result());
         
        
        
    }
 
    $link -> close();
    return 1;
}




$time_start = microtime(true);


// detction  of start date
$start_date=0;
if(detect_empty() == 0 )
{
     $start_date= detect_first_date();
     if($start_date == "")
     {
         $start_date=date('Y-m-d' ,strtotime('-1 day') );
     }
 
}
else
{
    
    $start_date = date('Y-m-d', strtotime('+1 day', strtotime(detect_working_date())));
    //var_dump($start_date);
 

    
}

$startTime = strtotime( $start_date);
$endTime    = strtotime( date('Y-m-d' ,strtotime('-1 day') ) );

// command for fixing  one or more days
if($argv[1] != "")
{

    $startTime = strtotime( trim($argv[1]));
    $endTime =strtotime( trim($argv[1]));
    
    
    
    $year_a = date("Y",strtotime($argv[1]));
    $month_a = date("m",strtotime($argv[1]));
    $day_a = date("d",strtotime($argv[1]));
    // delete old data
    $link->query("DELETE FROM `traffic_day_statistics` WHERE `Year` = $year_a AND `Month` =$month_a  AND `Day` =$day_a;");
    
    
}
 



$link -> close();

// Loop between timestamps, 24 hours at a time
for ( $i = $startTime; $i <= $endTime; $i = $i + 86400 ) 
{
    $thisDate = date( 'Y-m-d', $i ); // 2010-05-01, 2010-05-02, etc
    
    echo "--- Now calulating : ". $thisDate." ---\r\n";
    
 
    calculate_day($thisDate);



 
}


// Display Script End time
$time_end = microtime(true);

//dividing with 60 will give the execution time in minutes other wise seconds
$execution_time = ($time_end - $time_start)/60;

//execution time of the script
echo 'Total Execution Time: '.$execution_time.' Mins '.'\r\n';





/*
echo "<pre>";
var_dump($tables);
*/


//echo json_encode ($json_array);

