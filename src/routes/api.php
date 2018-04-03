<?php

   use App\Models\User;

   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;

   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;


   $container = $app->getContainer();

   $container['logger'] = function ($c) {
      // create a log channel
      $log = new Logger('api');
      $log->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::INFO));

      return $log;
   };

   /**
      * This method restricts access to addresses. <br/>
      * <b>post: </b>To access is required a valid token.
   */
   $app->add(new \Slim\Middleware\JwtAuthentication([
      // The secret key
      "secret" => SECRET,
      "rules" => [
         new \Slim\Middleware\JwtAuthentication\RequestPathRule([
            // Degenerate access to '/webresources'
            "path" => "/webresources",
            // It allows access to 'login' without a token
            "passthrough" => [
               "/webresources/mobile_app/ping",
               "/webresources/mobile_app/login",
               "/webresources/mobile_app/register"
            ]
         ])
      ]
   ]));

   /**
   * This method a url group. <br/>
   * <b>post: </b>establishes the base url '/public/webresources/mobile_app/'.
   */
   $app->group('/webresources/mobile_app', function () use ($app) {
      /**
      * This method is used for testing the api.<br/>
      * <b>post: </b> http://localhost/api/public/webresources/mobile_app/ping
      */
      $app->get('/ping', function (Request $request, Response $response) {
         echo "pong";
         // return $response;
      }); 

      /**
     * This method gets a user into the database.
     * @param string $user - username
     * @param string $pass - password
     * @param int $country - country id
     */
   $app->get('/register', function (Request $request, Response $response) {

      $newUser = array(
         'guid' => uniqid(),
         'username' => $request->getParam("email"),
         'email' => $request->getParam("email"),
         'password' => password_hash($request->getParam("password"), PASSWORD_DEFAULT),
         'id_district' => (int) $request->getParam("district"),
         'id_vehicle' => (int) $request->getParam("vehicle"),
         'name' => $request->getParam('name'),
         'mobile' => $request->getParam("mobile"),
         'age' => $request->getParam("age"),
         'gender' => $request->getParam("gender"),
         'pan_card' => $request->getParam("pan_card"),
         'total_vehicle' => $request->getParam("total_vehicle"),
         'total_male' => $request->getParam("total_male"),
         'total_female' => $request->getParam("total_female"),
         'type' => 'General'
         );
      
      // User Valid Code
      $code = $request->getParam("code");


      try {
         
         $result = User::create($newUser);

        // If user has been registered
         if ($result) {
            $data['status'] = "Your account has been successfully created.";
         } else {
            $data['status'] = "Error: Your account cannot be created at this time. Please try again later.";
         }
        
        $response = $response->withHeader('Content-Type','application/json');
        $response = $response->withStatus(200);
        $response = $response->withJson($data);
        return $response;
      } catch (PDOException $e) {
        $this['logger']->error("DataBase Error.<br/>" . $e->getMessage());
      } catch (Exception $e) {
        $this['logger']->error("General Error.<br/>" . $e->getMessage());
      } finally {
        // Destroy the database connection
        $conn = null;
      }
   }); 

  });

?>
