<?php
  $version="AKKEN_8_1_0_1676.log";

  $filepath=$path.$file.$version;

  $contents="";

  $contents.="\n\n=======================================\n";

  $contents.=$companyuser."\n";

  $contents.="=======================================\n";

  /* QUERY to Create Table to store user department permissions for FO and BO */

  $query  = "CREATE TABLE `department_permission` (
            `sno` INT(11) NOT NULL AUTO_INCREMENT,
            `dept_sno` INT(11) NOT NULL DEFAULT '0' COMMENT 'department table sno',
            `permission` VARCHAR(15) NOT NULL DEFAULT '0' COMMENT 'users table username',
            `type` VARCHAR(2) NOT NULL DEFAULT '' COMMENT 'FO is Front Office, BO is Back Office',
            PRIMARY KEY (`sno`),
            KEY `dept_sno` (`dept_sno`),
            KEY `permission` (`permission`),
            KEY `type` (`type`)
          )";
  mysql_query($query, $db);
  $contents .= "\n".$query."\n".mysql_error($db);

  /* QUERY to Create Table to store modificaton of department permissions table */

  $query  = "CREATE TABLE `his_department_permission` (
            `sno` INT(11) NOT NULL AUTO_INCREMENT,
            `dept_sno` INT(11) NOT NULL DEFAULT '0' COMMENT 'department table sno',
            `permission` INT(11) NOT NULL DEFAULT '0' COMMENT 'users table username',
            `type` VARCHAR(2) NOT NULL DEFAULT '' COMMENT 'FO is Front Office, BO is Back Office',
            `muser` INT(11) NOT NULL DEFAULT '0' COMMENT 'Mofified username will be stored',
            `mdate` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Mofified date and time will be stored',
            PRIMARY KEY (`sno`),
            KEY `dept_sno` (`dept_sno`),
            KEY `permission` (`permission`),
            KEY `type` (`type`),
            KEY `muser` (`muser`)
          )";
  mysql_query($query, $db);
  $contents .= "\n".$query."\n".mysql_error($db);

  /* Insert into department_permission from department table as BO */

  $select = "SELECT * FROM department ";
  $result = mysql_query($select, $db);
  $contents .= "\n".$select."\n".mysql_error($db);

  while ($row = mysql_fetch_array($result)) {
    
    $dept_Sno = $row['sno'];
    $dept_permission = $row['permission'];
    $dept_permission_ary = explode(",", $dept_permission);
    foreach ($dept_permission_ary as $key => $usrAccess) {
        
      $insert = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$usrAccess."','BO')";
      mysql_query($insert, $db);
      $contents .= "\n".$insert."\n".mysql_error($db);
    }

  }

  /* INSERT into department_permission for all users in the users table FO */

  $select_usr = "SELECT * FROM users WHERE usertype !='' AND status !='DA' ";
  $result_usr = mysql_query($select_usr, $db);
  $contents .= "\n".$select_usr."\n".mysql_error($db);

  while ($usrrow = mysql_fetch_array($result_usr)) {
    
    $usrAccess = $usrrow['username'];

    $select_dept = "SELECT * FROM department ";
    $result_dept = mysql_query($select_dept, $db);
    $contents .= "\n".$select_dept."\n".mysql_error($db);

    while ($row_dept = mysql_fetch_array($result_dept)) {
      $dept_Sno = $row_dept['sno'];

      $insertFO = "INSERT INTO department_permission (dept_sno,permission,type) VALUES ('".$dept_Sno."','".$usrAccess."','FO')";
      mysql_query($insertFO, $db);
      $contents .= "\n".$insertFO."\n".mysql_error($db);
    }
  }
?>