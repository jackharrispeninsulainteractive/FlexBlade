<?php

namespace FlexBlade\Responses\Lucent;

use Exception;
use FlexBlade\Blade\BladeCompiler;
use Lucent\Http\JsonResponse;
use Lucent\Http\Request;

class BladeController
{

    public function getComponent(BladeCompiler $compiler,Request $request) : JsonResponse
    {
        $response = new JsonResponse();

        if($request->input("component") === null){
            $response->setMessage("Missing component name");
            $response->setStatusCode(400);
            $response->addContent("html","");
            return $response;
        }

        $name = str_replace(".",DIRECTORY_SEPARATOR,$request->input("component"));
        $props = $request->except(["component"]);

        try{
            $response->addContent("html",$compiler->render("Blade/Components/".$name,$props));
        }catch (Exception $e){
            $response->setMessage($e->getMessage());
            $response->setOutcome("false");
            $response->setStatusCode(500);
        }
        return $response;
    }

}