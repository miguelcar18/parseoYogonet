<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es" dir="ltr">

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
<body>
<?php
    $arreglo = parsearApuestasDeportivasAction();

    function parsearApuestasDeportivasAction() 
    {

        //$em = $this -> getDoctrine() -> getManager();
        $contadorImagenes = 0;
        $contadorPronosticos = 0;
        $contadorNoticias = 0;

        //Ruta del listado de noticias de la web a analizar
        $url = 'http://www.yogonet.com/latinoamerica/';

        //Con la funcion file_get_contents se obtiene todo el html que devuelve la web señalada
        ini_set('max_execution_time', 300);
        $htm = file_get_contents($url);
        //Luego de estudiar el codigo fuente de la web, se descubre que todos los elementos de interes
        //Se encuentran dentro del div LATERAL_IZQUIERDO_DETALLE, por ende se hace un explode para poder
        //Obtener los argumentos que esten antes o despues (adentro del div)
        
        //Noticias Principales en <div class="panel-col-top panel-panel">
        $str = 'panel-col-top panel-panel';
        $arr = explode($str, $htm);
        $arr = explode('panel-col-bottom panel-panel', $arr[1]);

        $contenido = $arr[0];

        //var_dump($contenido);exit;

        //Una vez adentro del div de nuestro interes, se observa que todos los enlaces que se necesitan
        //Comienzan por detalle_noticia.php?id=
        $enlaces = explode("\"/latinoamerica/", $contenido);

        //Se hace un explode para obtener todos los codigos html que comiencen justo despues de http://www.apuestas-deportivas.es/pronostico/

        $idNoticias = array();
        //Se realiza un ciclo for para obtener todos los ids de las noticias
        for ($i=1; $i < count($enlaces); $i++) 
        { 
            $cadena = $enlaces[$i];
            //En estas cadenas resultados del ultimo explode hay mas contenido del que nos interesa
            //Un ejemplo de un link es detalle_noticia.php?id=83851" por ende se sabe que el codigo del id
            //termina justo antes de la comilla " para esto se usa strpos para ubicar la posicion y luego extraer el id
            $posicionFinal = strpos($cadena, '"');
            $id = substr($cadena, 0, $posicionFinal);
            //Se comienca en $i-1 porque el primer link que devuelve esta web siempre es vacio y se requieren 
            //son los numeros de los id
            $idNoticias[$i-1] = $id;
        }

        /*
        Para el listado de noticias de sector del juego por cada cuadricula hay 3 enlaces,
        uno en el texto, otro en la imagen y otro en el titulo, por ende despues de buscar los links
        apareceran duplicados, para evitar este problema se usa la funcion array_unique que elimina
        los duplicados, pero conserva las antiguas claves, por esto se debe usar foreach y no un for normal
        */
        $idNoticias = array_unique($idNoticias);
       
        /*
        Se procede a recorrer cada uno de los enlaces para extraer la data y almacenarla en la base de datos
        Se busca recorrer el listado de urls que se genero ejemplo:
        http://sectordeljuego.com/detalle_noticia.php?id=83851
        http://sectordeljuego.com/detalle_noticia.php?id=83850
        http://sectordeljuego.com/detalle_noticia.php?id=83849
        .
        .
        .
        Y asi sucesivamente, se ira recorriendo y obteniendo los textos y descargando una imagen por 
        cada articulo
        */

        foreach ($idNoticias as $key => $value) 
        {
            ini_set('max_execution_time', 300);
            // Tener en cuenta que no es igual la ruta lista_noticias a detalle_noticia
            $urlNoticia = "http://www.yogonet.com/latinoamerica/".$value;
            
            $html = file_get_contents($urlNoticia);
            $busqueda = 'buildmode-full';
            $html = explode($busqueda, $html);
            $html = explode('field-field-fuente', $html[1]);
            $html = $html[0];
            
            //Titulo
            $busquedaTituloInicio = 'field-title-noticia-importada">';
            $busquedaTituloFin='<div id="plugins-sociales">';
            $parteTitulo = explode($busquedaTituloInicio, $html);
            $parteTitulo = explode($busquedaTituloFin, $parteTitulo[1]);
            $titulo = $parteTitulo[0];

            $busquedaTituloInicio = 'field-item">';
            $busquedaTituloFin='</div>';

            $parteTitulo = explode($busquedaTituloInicio, $titulo);
            $parteTitulo = explode($busquedaTituloFin, $parteTitulo[1]);
            $titulo = $parteTitulo[0];

            echo'<br>'.$titulo;

            $busquedaInicio = 'http://www.yogonet.com/latinoamerica/sites/default/files/noticias/imagenes/';
            $busquedaFin='?';

            $elemento = explode($busquedaInicio, $html);
            if(count($elemento)>1){
            $elemento = explode($busquedaFin, $elemento[1]);
            $imagen = $elemento[0];
            $imagen = $busquedaInicio .$imagen;

            $urlImagen = $imagen;
            $extension = explode(".", $urlImagen);
            $extension = $extension[count($extension) - 1];
            //$path = $this -> container -> getParameter('kernel.root_dir') . '/../web/' . $this -> getUploadDir();
            $path = 'C:/wamp/www/parseoYogonet/uploads';
            $nombreImagen = sha1($urlImagen);

            ini_set('max_execution_time', 300);
            $ch = curl_init($urlImagen);
            $fp = fopen(sprintf('%s/%s.%s', $path, $nombreImagen, $extension), 'wb');
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);

            echo'<br>'.$nombreImagen.'.'.$extension;
            }


            
        }
    }
?>
</body>
</html>
    