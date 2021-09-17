<?php
    
    error_reporting(E_ALL);
    ini_set('display_errors', '1');

    include '../setup.php';
    $sn = $_GET['sn'];

    $tax_rate = 0.1025;

    //GET fields from orders
    $pdo = ord_connect();
    $stmnt = $pdo -> prepare("select * from orders where sn = :sn");
    $stmnt -> execute(['sn' => $sn]);

    foreach ($stmnt as $i){
        $budget = $i["budget"];
        $paytype = $i["paytype"];
        $vendor = $i["vendor"];
        if($i["rush"] == 1){$rush = ' - RUSH - ';}else{$rush = null;}
        $needby = $i["needby"];
        $comments = $i["comments"];
        $purpose = $i["purpose"];
        if ($i["taxexempt"]==1) {
          $taxexemptcheckbox = 'checked'; 
        } else {
         $taxexemptcheckbox = 'unchecked';
       }
       $supp_docs = $i["supp_docs"];
       if ($i["taxexempt_nameongrant"]==1){$taxexemptnameongrantcheckbox = 'checked';}else{$taxexemptnameongrantcheckbox = 'unchecked';}
       $taxexempt_statement = $i["taxexempt_statement"];
    }
    
    //GET fields from vendors
    $stmnt = $pdo -> prepare("select * from vendors where vendor = :vendor");
    $stmnt -> execute(['vendor' => $vendor]);
    foreach ($stmnt as $i){
        $address = $i["address"];
        $contact = $i["contact"];
        $phone = $i["phone"];
        $email = $i["email"];
        $url = $i["url"];
    }  
    
    //GET fields from products
    $pn = array();
    $description = array();
    $quantity = array();
    $unitprice = array();
    $totalprice = array();
    $stmnt = $pdo -> prepare("select * from products where sn = :sn");
    $stmnt -> execute(['sn' => $sn]);
    foreach ($stmnt as $i){
        array_push($pn,$i["pn"]);
        array_push($description,$i["description"]);
        array_push($quantity,$i["quantity"]);
        array_push($unitprice,$i["unitPrice"]);
        array_push($totalprice,$i["totalPrice"]);
    }  

    $ordersubtotal = array_sum($totalprice);
    $ordertax = round($ordersubtotal * $tax_rate,2);
    $ordertotal = $ordersubtotal + $ordertax;

    for ($i=0;$i<=9;$i++){
      if (!isset($pn[$i])){array_push($pn,null);}
      if (!isset($description[$i])){array_push($description,null);}
      if (!isset($quantity[$i])){array_push($quantity,null);}
      if (!isset($unitprice[$i])){array_push($unitprice,null);}
      if (!isset($totalprice[$i])){array_push($totalprice,null);}
    }

    $pdo = null;

    //create purchase request in format similar to ESS purchase request excel file
    $shortdate = substr($sn,0,8);
    echo '
    <html>
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" type="text/css" href="../style_PO.css">
    </head>
    <body>
    <div id="orders">
    <div id="orders_purchase_method">
    <strong>Purchase Method</strong>: '.$paytype.'
    </div>
    <div id="orders_title">
    IsoLab Purchase Request<span id="rush">'.$rush.'</span>
    <div id="orders_subtitle">
    IsoLab - Johnson Hall 302 - Earth and Space Sciences<br />
    <strong>IsoLab Order Serial Number:</strong> '.$sn.'<br />
    </div>
    </div>
    <div id="orders_rightcolumn">
    <div id="orders_POnum">
    <strong>P.O, Trans., or E.P. #:</strong>
    </div>
    <div id="orders_approval">
    <strong>Department Approval:</strong>
    </div>
    <div id="orders_approval">
    <strong>PI Budget Approval:</strong>
    </div>
    <div id="orders_budget">
    <strong>Budget:</strong> '.$budget.'<br />
    </div>
    <div class="orders_ship_bill_to">
    <strong>Ship To:</strong><br />
    UW Earth and Space Sciences<br />
    Johnson Hall room 70<br />
    Box 351310<br />
    4000 15th Ave. NE (optional)<br />
    Seattle, WA  98195-1310<br />
    206-543-1190 Tel<br />
    206-543-0489 Fax<br />
    esspurch@uw.edu<br />
    </div>
    <div class="orders_ship_bill_to">
    <strong>Bill To (ProCard):</strong><br />
    UW Earth and Space Sciences<br />
    Johnson Hall room 70<br />
    Box 351310<br />
    4000 15th Ave. NE (optional)<br />
    Seattle, WA  98195-1310<br />
    </div>  
    </div>
    <div id="orders_contact">
    <strong>Name:</strong> Andrew Schauer<br />
    <strong>Phone:</strong> 3.6327<br />
    <strong>email:</strong> aschauer@uw.edu<br /><br />
    <strong>Request Date: </strong>'.$shortdate.'<br />
    <strong>Need by: </strong>'.$needby.'
    </div>
    <div id="orders_vendor">
    <strong>Vendor Name:</strong> '.$vendor.'<br />
    <strong>Vendor Address:</strong> '.$address.'<br />
    <strong>Vendor Contact:</strong> '.$contact.'<br />
    <strong>Vendor Phone:</strong> '.$phone.'<br />
    <strong>Vendor Email:</strong> '.$email.'<br />
    <strong>Vendor url:</strong> '.$url.'<br />
    </div>
    <div id="orders_purpose">
    <strong>Purpose: </strong>'.$purpose.'<br />
    </div>
    <div id="orders_taxexempt">
    <div id="orders_taxexempt_checkboxes">
    <strong>M &amp; E Tax Exempt</strong>
    <br /><strong>Tax exempt: </strong><input type="checkbox" name="taxexempt" value="1" '.$taxexemptcheckbox.' disabled="disabled">
    <br /><strong>Named on grant: </strong><input type="checkbox" name="taxexempt" value="1" '.$taxexemptnameongrantcheckbox.' disabled="disabled">
    </div>
    <strong>Tax exempt statement:</strong> '.$taxexempt_statement.'
    </div>
    <div id="orders_lineitems">
    <table>
    <tr>
    <td class="orders_colhead_pn">Part Number</td>
    <td class="orders_colhead_des">Description</td>
    <td class="orders_colhead_qty">Quantity</td>
    <td class="orders_colhead_ea">Unit Price</td>
    <td class="orders_colhead_tot">Total Price</td>
    </tr>
    <tr class="orders_lineitems_color">
    <td class="dot">'.$pn[0].'</td>
    <td class="dot">'.$description[0].'</td>
    <td class="dot">'.$quantity[0].'</td>
    <td class="dot">$'.$unitprice[0].'</td>
    <td class="dot">$'.$totalprice[0].'</td>
    </tr>
    <tr>
    <td class="dot">'.$pn[1].'</td>
    <td class="dot">'.$description[1].'</td>
    <td class="dot">'.$quantity[1].'</td>
    <td class="dot">$'.$unitprice[1].'</td>
    <td class="dot">$'.$totalprice[1].'</td>
    </tr>
    <tr class="orders_lineitems_color">
    <td class="dot">'.$pn[2].'</td>
    <td class="dot">'.$description[2].'</td>
    <td class="dot">'.$quantity[2].'</td>
    <td class="dot">$'.$unitprice[2].'</td>
    <td class="dot">$'.$totalprice[2].'</td>
    </tr>
    <tr>
    <td class="dot">'.$pn[3].'</td>
    <td class="dot">'.$description[3].'</td>
    <td class="dot">'.$quantity[3].'</td>
    <td class="dot">$'.$unitprice[3].'</td>
    <td class="dot">$'.$totalprice[3].'</td>
    </tr>
    <tr class="orders_lineitems_color">
    <td class="dot">'.$pn[4].'</td>
    <td class="dot">'.$description[4].'</td>
    <td class="dot">'.$quantity[4].'</td>
    <td class="dot">$'.$unitprice[4].'</td>
    <td class="dot">$'.$totalprice[4].'</td>
    </tr>
    <tr>
    <td class="dot">'.$pn[5].'</td>
    <td class="dot">'.$description[5].'</td>
    <td class="dot">'.$quantity[5].'</td>
    <td class="dot">$'.$unitprice[5].'</td>
    <td class="dot">$'.$totalprice[5].'</td>
    </tr>
    <tr class="orders_lineitems_color">
    <td class="dot">'.$pn[6].'</td>
    <td class="dot">'.$description[6].'</td>
    <td class="dot">'.$quantity[6].'</td>
    <td class="dot">$'.$unitprice[6].'</td>
    <td class="dot">$'.$totalprice[6].'</td>
    </tr>
    <tr>
    <td class="dot">'.$pn[7].'</td>
    <td class="dot">'.$description[7].'</td>
    <td class="dot">'.$quantity[7].'</td>
    <td class="dot">$'.$unitprice[7].'</td>
    <td class="dot">$'.$totalprice[7].'</td>
    </tr>
    <tr class="orders_lineitems_color">
    <td class="dot">'.$pn[8].'</td>
    <td class="dot">'.$description[8].'</td>
    <td class="dot">'.$quantity[8].'</td>
    <td class="dot">$'.$unitprice[8].'</td>
    <td class="dot">$'.$totalprice[8].'</td>
    </tr>
    <tr>
    <td class="dot">'.$pn[9].'</td>
    <td class="dot">'.$description[9].'</td>
    <td class="dot">'.$quantity[9].'</td>
    <td class="dot">$'.$unitprice[9].'</td>
    <td class="dot">$'.$totalprice[9].'</td>
    </tr>
    </table>
    </div>

    <div id="orders_comments">
      <strong>Comments: </strong>'.$comments.'
    </div>  
    <div id="orders_subtotals">
      <table>
      <tr><td>Subtotal</td><td class="orders_colhead_subtotal">$'.$ordersubtotal.'</td></tr>
      <tr><td>Estimated Shipping</td><td class="dot2"> </td></tr>
      <tr><td>Tax at 10.25%</td><td class="dot2">$'.$ordertax.'</td></tr>
      <tr><td><strong>Total</strong></td><td class="dot2"><strong>$'.$ordertotal.'</strong></td></tr>
      </table>
    </div>

    <div id="supp_docs">
      <strong>Supporting documents: </strong><a href="'.$abs_url.'PurchaseRequests/supp_docs/'.$supp_docs.'">'.$supp_docs.'</a>
    </div>
    </div>
    <div style="float:right; font-size:10px; text-align:right; width:730px;">IsoLab Purchase Request v8.0  210406</div>
    </body>
    </html>';

?>
