<?php
/*
**
*/

$response=[
    'status'=>'error',
    'data'=>null
];
$db = new PDO('mysql:host=localhost;dbname=sekolah', 'root');
$queryString=[];
$rawBody=file_get_contents('php://input', 'r');
$body=json_decode($rawBody, true);

function getId(){
    if(!strpos($_SERVER['REQUEST_URI'], '?'))return false;
    //satu path
$path=explode('?', $_SERVER['REQUEST_URI'])[1];
$segments=explode('/', $path);

return +$segments[1];
//variabel yg dibuang ke output
//var_dump($segments[1]);

// //satu huruf 
// $path=explode('?', $_SERVER['REQUEST_URI'])[1];
// var_dump($path[1]);

}
switch(strtolower($_SERVER['REQUEST_METHOD'])){
    case 'post':
        try{
            if(!isset($body['nis'],$body['nama'], $body['id_jurusan'], $body['id_walikelas'])){
                throw new InvalidArgumentException('Invalid form');
            }
            //eksekusi insert data ke database 
            $stmt=$db->prepare('INSERT into siswa (ID, NAMA, ID_JURUSAN, ID_WALIKELAS)
            values (:nis, :nama, :id_j, :id_w)');
            
            $stmt->execute([
                ':nis'=>$body['nis'],
                ':nama'=>$body['nama'],
                ':id_j'=>$body['id_jurusan'],
                ':id_w'=>$body['id_walikelas'],     
            ]);
            http_response_code(201);
            $response['status']='SUKSES';
            $response['data']=[];
        } catch(Throwable $error){
            if($error instanceof InvalidArgumentException){
                http_response_code(400);
            }else{
                http_response_code(500);
            }

            $response['error']=$error->getMessage();
        }

        break;

case 'delete':
    $id=getId();

    if(!$id){
        http_response_code(404);
        break;
    }
    $stmt=$db->prepare('SELECT * FROM siswa where ID = :id');
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if(!$stmt->rowCount()){
        http_response_code(404);
        break;
    }
    $stmt=$db->prepare('DELETE FROM siswa where ID = :id');
    $stmt->execute([':id'=>$id]);
    http_response_code(204);
break;

    case 'get':
        $id=getId();

        if($id){
            $stmt = $db->prepare('SELECT * from siswa where ID = :id');
            $stmt->execute([':id'=>$id]);
            $data=$stmt->fetch(PDO::FETCH_ASSOC);
        }else{
            $stmt = $db->
        query('SELECT s.ID, s.NAMA, j.NAMA NAMA_JURUSAN, w.NAMA NAMA_WALIKELAS
                FROM siswa s
                JOIN jurusan j on j.ID = s.ID_JURUSAN
                JOIN walikelas w on w.ID=s.ID_WALIKELAS');
        
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }
                if(!empty($data)){
            $response['status']='SUKSES';
            $response['data']=$data;

        
            // foreach($data as $i => $row){
            //     $response['data'][$i]=[
            //         'ID' => $row ['ID'],
            //         'nama' => $row ['NAMA'],
            //         'nama_jurusan' => $row ['NAMA_JURUSAN'],
            //         'nama_walikelas' => $row ['NAMA_WALIKELAS'],
            //     ];
            // }
        }else{ //jika datanya kosong maka HTTP Status codenya adalah 404 
            http_response_code(404);
        }
        break;

    default:
        http_response_code(503);
        break;
}


header('Content-Type:application/json');
echo json_encode($response);