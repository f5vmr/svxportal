<?php
include "../config.php";
include '../function.php';
define_settings();
mysqli_set_charset($link,"utf8");

if( $_SESSION['loginid'] >0 )
{
   
    
    
    if($_POST['Information_edit'] == "1")
    {
    
        
        $link->begin_transaction();
        $link->autocommit(FALSE);
        
        // prepare statement (for multiple inserts) only once
        
        $station =htmlentities($_POST['station']);
//        $station= htmlspecialchars($station, ENT_QUOTES, "UTF-8");
        $station= $link->real_escape_string($station);
        

        
        
        
        $id= $link->real_escape_string($_POST['id']);
        
        $station_id =page_id_to_station_id($id);

        $permission_rw =check_permission_station_RW($station_id,$_SESSION['loginid']);

        
        if($permission_rw >0)
        {
            $link->query(" UPDATE `Information_page` SET `Html` = '$station' where id ='$id' ");
            echo " UPDATE `Information_page` SET `Html` = '$station' where id ='$id' ";
        }

        
        $link->commit();
        $link->close();
        
    }
    
    if($_POST['Hardware_edit'] == "1")
    {
        
        
        $link->begin_transaction();
        $link->autocommit(FALSE);
        
        // prepare statement (for multiple inserts) only once
        

        $station =htmlentities($_POST['hardware']);
        //        $station= htmlspecialchars($station, ENT_QUOTES, "UTF-8");
        $station= $link->real_escape_string($station);
        

        
        

        $id= $link->real_escape_string($_POST['id']);
        
        
        $station_id =page_id_to_station_id($id);
        $permission_rw =check_permission_station_RW($station_id,$_SESSION['loginid']);
        
        if($permission_rw >0)
            $link->query(" UPDATE `Information_page` SET `Hardware_page` = '$station' where id ='$id' ");
        
        
        
        $link->commit();
        $link->close();
        
    }
    if($_POST['Mew_DTNF'] == "1")
    {
        

        $stid= $link->real_escape_string($_POST['station_id']);
        $station_name = $link->real_escape_string($_POST['Station_name']);
        $command= $link->real_escape_string($_POST['command']);
        $Description= $link->real_escape_string($_POST['Description']);
        $Category= $link->real_escape_string($_POST['Category']);


        $permission_rw = check_permission_station_RW($stid,$_SESSION['loginid']);
        
        if($permission_rw >0)
            $link->query("INSERT INTO `dtmf_command` (`id`, `station_name`, `station_id`, `Command`, `Description`,`Category`) VALUES (NULL, '$station_name', '$stid', '$command', '$Description','$Category'); ");
        
    }
    if($_POST['Remove_DTMF'] == "1")
    {
        
        
        $stid= $link->real_escape_string($_POST['station_id']);
        $DMF_ID = $link->real_escape_string($_POST['DMF_ID']);
   
        
        

        $station_id = DTMF_ID_TO_STATION($DMF_ID);
        echo $station_id;
        $permission_rw = check_permission_station_RW($station_id,$_SESSION['loginid']);
        
        if($permission_rw >0)
            $link->query("DELETE FROM `dtmf_command` WHERE `dtmf_command`.`id` = '$DMF_ID'");
        
        
    }
    
    if($_POST['Update_DTMF'] == "1")
    {
        
        $command= $link->real_escape_string($_POST['command']);
        $Description= $link->real_escape_string($_POST['Description']);
        $Category= $link->real_escape_string($_POST['Category']);
       
        $DMF_ID = $link->real_escape_string($_POST['dtmf_id']);
        
        
        


        $link->query("UPDATE `dtmf_command` SET `Command` = '$command', `Description` = '$Description' ,`Category` ='$Category' WHERE `dtmf_command`.`id` = '$DMF_ID'; ");
        
        
    }
    
    if($_POST['update_setings'] == "1")
    {
        
        $Driver= $link->real_escape_string($_POST['Driver']);
        $radio_image= $link->real_escape_string($_POST['radio_image']);
        $GrafanaUrl= $link->real_escape_string($_POST['GrafanaUrl']);

        
        $Update_id = $link->real_escape_string($_POST['Update_id']);
        $station_id =page_id_to_station_id($Update_id);
        $permission_rw =check_permission_station_RW($station_id,$_SESSION['loginid']);
        
        if($permission_rw >0)
        
           $link->query("UPDATE `Information_page` SET `Module` = '$Driver', `Image` = '$radio_image' , GrafanaUrl= '$GrafanaUrl' WHERE `Information_page`.`id` = '$Update_id'; ");
        
        
    }
    if($_POST['Insert_station_status'] == "1")
    {
        
        echo "3";
        $stid= $link->real_escape_string($_POST['station_id']);
        $Type = $link->real_escape_string($_POST['Type']);
        $date= $link->real_escape_string($_POST['date']);
        $command= $link->real_escape_string($_POST['command']);
        
        $phptime = strtotime($date);
        
        $mysqltime = date ('Y-m-d H:i:s', $phptime); 



        $permission_rw =check_permission_station_RW($stid,$_SESSION['loginid']);
        
        if($permission_rw >0)
         $link->query("INSERT INTO `Operation_log` (`id`, `station_id`, `Type`, `Date`, `Message`) VALUES (NULL, '$stid', '$Type', '$mysqltime', '$command'); ");
        
    }
    
    
    
    
    
    
    
    
}
?>