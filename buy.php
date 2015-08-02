<?php
session_start();
?>

<form action="buy.php" method="GET">
<fieldset>
	<legend>Find products:</legend>
	
<select name="category">
<?php
error_reporting(E_ALL);
ini_set('display_errors','On');
$xmlstr = file_get_contents('http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/CategoryTree?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&visitorUserAgent&visitorIPAddress&trackingId=7000610&categoryId=72&showAllDescendants=true');
$xml = new SimpleXMLElement($xmlstr);
//header('Content-Type: text/xml');
//print $xmlstr;
echo "<option value=".$xml->category['id'].">".$xml->category->name."</option>";
echo "<optgroup label= '".$xml->category->name.":-'/>";
foreach ($xml->category->categories->category as $child)
{
echo "<option value=".$child['id'].">".$child->name."</option>";
echo "<optgroup label='".$child->name.":'/>";
foreach($child->categories->category as $subchild)
{
echo "<option value=".$subchild['id'].">".$subchild->name."</option>";
}

}
?>
<input type="text" name="keyword"/>
<input type="submit" value="search"/>
<input type="submit" name="empty_basket" value="Empty Basket"/>
</select>
</fieldset>
</form>
<?php
if(isset($_GET['keyword'])){
$values=$_GET['keyword'];
$value=str_replace ( " ", "+", $values );
//echo $_GET['category'];
$cat=$_GET['category'];
$xmlstr = file_get_contents("http://sandbox.api.ebaycommercenetwork.com/publisher/3.0/rest/GeneralSearch?apiKey=78b0db8a-0ee1-4939-a2f9-d3cd95ec0fcc&trackingId=7000610&category=".$cat."&keyword=".$value."&numItems=20");
$xml = new SimpleXMLElement($xmlstr);
$_SESSION['s']=(string)$xmlstr; 
$tabular="<table border=1>";
foreach($xml->categories->category->items->product as $res){
$tabular .="<tr><td><a href=buy.php?buy=".$res["id"]."><img src=".$res->images->image->sourceURL."></a></td>";
$tabular .="<td>".$res->name."</td>";
$tabular .="<td>".$res->minPrice."</td>";
$tabular .="<td>".$res->fullDescription."</td></tr>";
}
$tabular.="</table>";																
echo $tabular;
}
function shoppingcart(){
$cart="<table border= 1>";
    $total = 0.0;
    if(isset($_SESSION['cost']))
    {
	
        $cost= $_SESSION['cost']; 
      
        foreach($cost as $product => $price)
        {
        
            $total = $total + $price;
        }
		}
		if(isset($_SESSION['items']))
		{
		$items= $_SESSION['items'];
        foreach($items as $product => $item)
        {
            $cart.= $item."<br/>";
        }
    }
    $cart.= "<tr><td>Total:</td><td>".$total."<center></td></tr>";
    $cart.="</table>";
    print $cart;
    
}
if(isset($_GET['buy']))
{
    $id= $_GET['buy'];
    $xmlstr1= $_SESSION['s'];
    $xml2= simplexml_load_string($xmlstr1);
    if(!isset($_SESSION['items']))
    {
       	$itemsarray= array();
        $costarray = array();
        $_SESSION['items']= $itemsarray;
        $_SESSION['cost']= $costarray;
    }
    else
    {
    	$itemsarray = $_SESSION['items'];
        $costarray = $_SESSION['cost'];
        
    }
    if(!isset($itemsarray[$id]))
	{
    foreach($xml2->categories->category->items->product as $res)
    {
        if($res['id']==$id)
        {
            $cart = "<tr> ";
            $cart.= "<td>";
            $cart .= "<a href=".$res->productOffersURL."><img src=".$res->images->image->sourceURL."></a></td>";
            $cart .= "<td>".$res->name."</td>";
            $cart .= "<td>".$res->minPrice."</td>";
			$cart .="<td>";
			$cart .="<a href=buy.php?delete=".$id.">Delete</a></td></tr>";
            $itemsarray[$id]= $cart;		
            $costarray[$id]= floatval ( strip_tags ( $res->minPrice ) );
        }
    }
    $_SESSION['cost']= $costarray;
    $_SESSION['items']= $itemsarray;
    
    }
  
    shoppingcart();
}
if(isset($_GET['delete']))
{
    $deleteId= $_GET['delete'];
    unset($_SESSION['items'][$deleteId]);
    unset( $_SESSION['cost'][$deleteId]);
    shoppingcart();
}
if(isset($_GET['empty_basket']))
{
   unset($_SESSION['items']);
    unset($_SESSION['cost']);
	session_unset();
	session_destroy();
    shoppingcart();     
}  

?>