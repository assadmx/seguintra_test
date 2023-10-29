<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class Login extends Controller
{
    public function index()
    {
        return view('login/index');
    }

    public function process()
    {
        $session = session();

        $username = $this->request->getVar('password');
        // Carga el modelo de usuario
        $UserModel = new \App\Models\UsuarioModel();
        $UsrvspruebaModel = new \App\Models\UsrvspruebaModel();
        $PreguntasModel = new \App\Models\PreguntasModel();
        $View_respusrsModel = new \App\Models\View_respusrsModel();
        $PruebasModel = new \App\Models\PruebasModel();
        $respuestasModel = new \App\Models\RespUsrModel();

        // Consulta la base de datos para verificar las credenciales del usuario
        $user = $UserModel->where('id', $username)->first();
        if ($user) {
            $contestado=$View_respusrsModel->where('usuario', $username)->findAll();
            if ($contestado){
                $prueba=$UsrvspruebaModel->encuentraprueba($user['id']);
                $preguntas=$PreguntasModel->encuentrapreguntas($prueba['prueba']);
                $minaprob=$PruebasModel->encuentraminaprob($prueba['prueba']);
                $correctas=$View_respusrsModel->encuentracorrectas ($prueba['prueba'],intval($username));
                $minaprobVal=floatval($minaprob['minaprob']);
                $conteocorrectas=count($correctas);
                
                $calificacion = round(($conteocorrectas*10)/count($preguntas),2);
                //var_dump($minaprob); die();
                //var_dump($minaprobVal); die();
                if ($calificacion>$minaprobVal) {
                    $msjaprob ="Felicidades, ha obtenido una calificación aprobatoria con la calificación de: ".$calificacion;
                } else{
                    $msjaprob ="Lo sentimos, usted no ha aprobado el examen con la calificación de: ".$calificacion;
                }
                $data =[
                    'usuario'=> $user['id'],
                    'nombre' => $user['nombre'],
                    'appat'  => $user['apppat'],
                    'prueba' => $prueba['prueba'],
                    'msjaprob' => $msjaprob
                ];
                
                return view('dashboard/calificacion',$data);
            }else{
                $session->set('isLoggedIn', true);
                $prueba=$UsrvspruebaModel->encuentraprueba($user['id']);
                $preguntas=$PreguntasModel->encuentrapreguntas($prueba['prueba']);
                $data =[
                    'usuario'=> $user['id'],
                    'nombre' => $user['nombre'],
                    'appat'  => $user['apppat'],
                    'prueba' => $prueba['prueba'],
                    'preguntas' => $preguntas
                ];
                //var_dump ($data); die();
                return view('dashboard/escritorio', $data);
            }
        } else {
            return redirect()->to('login')->with('error', 'Usuario incorrecto');
        }
    }
    
    public function logout()
    {
        $session = session();
        $session->remove('isLoggedIn');
        return redirect()->to('login');
    }

}

