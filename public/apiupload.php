<?php
$db=mysqli_connect('localhost','u1487570_api_pohonasuh','u1487570_api_pohonasuh','u1487570_api_pohonasuh');
// mengecek koneksi
if (!$db) {
    die("Koneksi gagal: " . mysqli_connect_error());
}



$id=$_POST['id'];
$image[]=$_FILES['image']['name'];
$tmpFile[]=$_FILES['image']['tmp_name'];
foreach ($image as $key => $value) {
    foreach ($tmpFile as $key => $tmpFilevalue) {
        if(move_uploaded_file($tmpFilevalue,'assets/'.$value)){

            $urlnya="https://rest.pohonasuh.org/assets/".$value;
             $save=$db->query("INSERT INTO image(idorder,urlnya)VALUES('".$id."','".$urlnya."')");
             if($save){
                echo json_encode(array(
                    "status"=>"Succes"
                ));
             }else{
                echo json_encode(array(
                    "status"=>"Field".mysqli.error($db)
                ));
             }
        }
    }
}
    

?>