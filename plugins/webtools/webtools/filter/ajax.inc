<?php
include ('functions.inc');

function filter_handle_delete () // handle a delete request
{
   $rcmail = rcmail::get_instance();
   if (have_file (".mailfilter", $result))      // grab main conf
   {
      $config = parse_main_config ($result);    // parse mein conf
      foreach ($config as $id => $filter)
         if ($filter['path'] == $_POST['filter']) unset ($config[$id]); // delete the filter we wanna remove
      $new_config = make_main_config ($config);  // remake the file
      write_file (".mailfilter", $new_config);   // write it
      $_POST['filter'] = str_replace ('.', '', $_POST['filter']);
      $rcmail->output->command('plugin.webtools.filter.delete.response', array('status' => 'success', 'deleted' => $_POST['filter'])); // let the js know we delted it
   }
}

function filter_handle_rename () // handle a rename request
{
   $rcmail = rcmail::get_instance();
   $success=(sanitize_filter_name($_POST['name']));
   if($success==true) { //if valid filter name
      if (have_file(".mailfilter", $result))  // grab main conf
      {
         $done = "false";
         $config = parse_main_config ($result);  // parse main conf
         foreach ($config as $id => $filter)
            if ($filter['path'] == $_POST['id'])
            {
               $config[$id]['name'] = $_POST['name'];
               $done = "true";
		    }
	     $new = make_main_config ($config);
         write_file (".mailfilter", $new);
         $rcmail->output->command('plugin.webtools.filter.rename.response', array('status' => $done, 'id' => str_replace(".", "", $_POST['id']), 'name'=>$_POST['name'])); 
	  }
   }else{
	   $rcmail->output->command('plugin.webtools.filter.rename.response', array('status' => $success, 'id' => '', 'name' => ''));
   }
}

function filter_handle_add () // ajax handle filter add
{
   $rcmail = rcmail::get_instance();
   $success = (sanitize_filter_name($_POST['filter']));
   //this is not foolproof
   $path = ".mailfilter-" . str_replace (array(" ", ";", "/", "\\", ":"), "", $_POST['filter']) . '-' . (count($config) + 1); // remove any shit we dont want in the filtername
   if (have_file (".mailfilter", $result)) // read in the main conf
   {
	  if($success==true) { //if valid filter name
      $config = parse_main_config ($result); // parse main conf
      $config[] = array ("name" => $_POST['filter'], "enabled" => "true", "path" => $path); // add in thenew filter
      $new_config = make_main_config ($config);  // make new conf
      write_file (".mailfilter", $new_config);  // write new conf
      write_file ($path, ''); // make empty file for newly created filter
      $rcmail->output->command('plugin.webtools.filter.add.response', array('status' => $success, 'name' => $_POST['filter'], 'path' => $path, 'asdf' => 'asdf')); // let the js know we did it
   }else{
	   $rcmail->output->command('plugin.webtools.filter.add.response', array('status' => $success, 'name' => '', 'path' => ''));
   }
   }
   else  // if this is the first filter
   {
	  if($success==true) { //if valid filter name
      $config[] = array ("name" => $_POST['filter'], "enabled" => "true", "path" => $path);
      $new_config = make_main_config ($config); 
      write_file (".mailfilter", $new_config);
      write_file ($path, '');
      $rcmail->output->command('plugin.webtools.filter.add.response', array('status' => $success, 'name' => $_POST['filter'], 'path' => $path));
   }else {
      $rcmail->output->command('plugin.webtools.filter.add.response', array('status' => $success, 'name' => '', 'path' => ''));
   }
}
}

function filter_handle_enable ()
{
   if ($_POST['uniq'] == 'true')
   {
      if (have_file (".mailfilter", $result))
      {
         $config = parse_main_config ($result); 
         if ($_POST['value'] == 'true')
            $new_config = make_main_config ($config, 1);
         else
            $new_config = make_main_config ($config);
         write_file (".mailfilter", $new_config);
      }      
      return;
   }
   $rcmail = rcmail::get_instance();
   if (have_file (".mailfilter", $result))
   {
      $config = parse_main_config ($result);
      foreach ($config as $id => $filter)                                                // find the right filter
         if ($filter['path'] == $_POST['filter'])
         {
            $config[$id]['enabled'] = $_POST['value'];  // set it to the value we were given
         }
      $new_config = make_main_config ($config);      // remake and
      write_file (".mailfilter", $new_config);       // write config
   }
   $rcmail->output->command('plugin.webtools.filter.enable.response', array('status' => 'success', 'filter' => $_POST['filter'])); // ajax response
}

?>
