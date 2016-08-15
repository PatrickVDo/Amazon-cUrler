<!DOCTYPE html>
<head>
    <link rel="stylesheet" type="text/css" href="amazon_curler.css">
</head>
<html>
<title>Top 10 Keyboards</title>
<body>
<h1>Top Ten Keyboards on Amazon<h1>
<p>This site uses SQLITE3 to analyze Amazon's selection of keyboards. You can use it to find the top 10 cheapest and newest keyboards. <br> Click on the links on the top of each column to sort by that column's criteria. The data on this site is being renewed regularly. <br> Visit regularly to see what new keyboards Amazon has to offer, or to check for price drops!
</p>
<?php         
	$user_site_name = "computer keyboard";
	$user_site_name = str_replace(" ", "+", $user_site_name);
    $url = "http://www.amazon.com/s/ref=nb_sb_noss?url=search-alias%3Daps&field-keywords=" . $user_site_name;    
    $results_page = curl($url);
    $results_page = scrape_between($results_page, "<ul id=\"s-results-list-atf\"", "</html>"); 
    $separate_results = explode("<li id=\"result_", $results_page);   
	$result_titles = array();


    $db = new SQLite3('sqlite3.db') or die("database error");

    // $db->exec("DROP TABLE amazoncurl");
    $db->exec("CREATE TABLE IF NOT EXISTS amazoncurl 
        (id integer primary key, name TEXT NOT NULL, price REAL NOT NULL, imageurl TEXT NOT NULL, dateadded INTEGER NOT NULL, dateaccessed TEXT NOT NULL, unique(name))");

         
    foreach ($separate_results as $separate_result) {
        if ($separate_result != "") {
            $result_name = scrape_between($separate_result, "s-access-title a-text-normal\">", "</h2>"); 
            $result_price = scrape_between($separate_result, "a-text-bold\">$", "</span>");
		    $result_image_url = scrape_between($separate_result, "<img alt=\"Product Details\" src=", " onload");
            $result_composite = $result_name . "</td><td><b>$" . $result_price . "</b></td><td><img src=" . $result_image_url . " alt = " . $result_image_url . " height=\"160\" width=\"160\">";
            $current_date = date('l jS \of F Y h:i:s A');
            $date_added = time();
            $result_name = str_replace("'", "''", $result_name);
            $result_price = (double)$result_price;
            if($result_name != "" && $result_price != "" && $result_image_url != ""){
                // $db->exec("INSERT INTO amazoncurl(name, price, imageurl, dateaccessed) VALUES ('$result_name', '$result_price', '$result_image_url', '$current_date')");
                $db->exec("INSERT OR IGNORE INTO amazoncurl(name, price, imageurl, dateadded, dateaccessed) VALUES ('$result_name', '$result_price', '$result_image_url', '$date_added', '$current_date')");
                
                array_push($result_titles, $result_composite);
            }
        }
    }
    
    // if($result_titles[0] != ""){
    //     echo("<table width=\"90%\" align=\"center\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>Name</td><td>Price</td><td>Image</td></tr><tr><td>");
    // 	echo implode("</td></tr><tr><td>",$result_titles);
    //     echo("</td></tr><table>");
    // }

    $field = "name";
    if(isset($_GET["field"])) {
        $field = $_GET["field"];
    }   


    if($field == "dateadded"){
        $stmt = $db->prepare("SELECT * FROM amazoncurl ORDER BY $field DESC LIMIT 10");
    }else{
        $stmt = $db->prepare("SELECT * FROM amazoncurl ORDER BY $field ASC LIMIT 10");
    }
    $result = $stmt->execute();

    echo "<table width=\"90%\" align=\"center\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\">";
    echo "<tr><td>
    <a href = 'amazon_database.php?field=name' title='Click here to sort by Name'>Name</a>
    </td><td>
    <a href = 'amazon_database.php?field=price' title='Click here to sort by Price'>Price</a>
    </td><td>
    <a href = 'amazon_database.php?field=dateadded' title='Click here to sort by Date Added'>Date Added</a>
    </td><td>
    Image
    </td></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC))
    {
        // echo $row['name'] . $row['price'] . PHP_EOL . '<br>';
        // echo 'Name: ' . $row['name'] . '  Price: ' . $row['price'] . '  dateaccessed: ' . $row['dateaccessed'] . '<br>';
        echo '<tr><td>' . $row['name'] . '</td><td>$' . $row['price'] . '</td><td>' . $row['dateaccessed'] . '</td><td><img src=' . $row['imageurl'] . ' alt = ' . $row['imageurl'] . ' height="160" width="160" </td></tr>';
        //<img src=" . $result_image_url . " alt = " . $result_image_url . " height=\"160\" width=\"160\"

          // $row[$i]['user_id'] = $res['user_id']; 
          // $row[$i]['username'] = $res['username']; 
          // $row[$i]['opt_status'] = $res['opt_status']; 

          // $i++; 
    }

    echo "</table>";


    function curl($url) {
        $options = Array(
            CURLOPT_RETURNTRANSFER => TRUE,  
            CURLOPT_FOLLOWLOCATION => TRUE,  
            CURLOPT_AUTOREFERER => TRUE, 
            CURLOPT_CONNECTTIMEOUT => 120,  
            CURLOPT_TIMEOUT => 120,  
            CURLOPT_MAXREDIRS => 10, 
            CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  
            CURLOPT_URL => $url, 
        );
         
        $ch = curl_init();  
        curl_setopt_array($ch, $options);   
        $data = curl_exec($ch); 
        curl_close($ch);    
        return $data;   
    }

    function scrape_between($data, $start, $end){
        $data = stristr($data, $start); 
        $data = substr($data, strlen($start));  
        $stop = stripos($data, $end);  
        $data = substr($data, 0, $stop);    
        return $data;   
    }
?>
</body>
</html>
