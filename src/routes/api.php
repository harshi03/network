<?php

   use App\Models\User;

   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;

   use Monolog\Logger;
   use Monolog\Handler\StreamHandler;

   use Respect\Validation\Validator as v;
   use Illuminate\Database\QueryException;


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
      // User Valid Code
      $code = $request->getParam("code");
      $validation = $this->validator->validate($request, [
         'name' => v::notEmpty(),
         'mobile' => v::noWhitespace()->notEmpty(),
         'email' => v::noWhitespace()->notEmpty(),
         'password' => v::noWhitespace()->notEmpty(),
         'age' => v::numeric()->positive()->notEmpty(),
         'gender' => v::notEmpty(),
         'district' => v::numeric()->positive()->notEmpty(),
         'pan_card' => v::noWhitespace()->notEmpty(),
         'vehicle' => v::numeric()->positive()->notEmpty(),
         'total_vehicle' => v::numeric()->positive()->notEmpty(),
         'total_male' => v::numeric()->positive()->notEmpty(),
         'total_female' => v::numeric()->positive()->notEmpty(),
         'type' => v::noWhitespace()->notEmpty(),
      ]);

      if($validation->isValid()) {
         $errors['message'] = "Validation errors in your request";
         $errors['errors'] = $validation->getErrors();
         $response = $response->withHeader('Content-Type','application/json');
         $response = $response->withStatus(400);
         $response = $response->withJson($errors);
         return $response;
      }

      $newUser = array(
         'guid' => uniqid(),
         'name' => $request->getParam('name'),
         'mobile' => $request->getParam("mobile"),
         'username' => $request->getParam("email"),
         'email' => $request->getParam("email"),
         'password' => password_hash($request->getParam("password"), PASSWORD_DEFAULT),
         'id_district' => (int) $request->getParam("district"),
         'id_vehicle' => (int) $request->getParam("vehicle"),
         'age' => $request->getParam("age"),
         'gender' => $request->getParam("gender"),
         'pan_card' => $request->getParam("pan_card"),
         'total_vehicle' => $request->getParam("total_vehicle"),
         'total_male' => $request->getParam("total_male"),
         'total_female' => $request->getParam("total_female"),
         'type' => $request->getParam("type")
      );

      $user = User::where('username', $request->getParam("email"))->first();

      if($user) {
         $data['message'] = 'The user already exist';
         $response = $response->withHeader('Content-Type','application/json');
         $response = $response->withStatus(409);
         $response = $response->withJson($data);
         return $response;
      }

      try {
         
         

         $result = User::create($newUser);

         // If user has been registered
         if ($result) {
            $data['message'] = "Your account has been successfully created.";
            // $data['result'] = $result; 
         } else {
            $data['status'] = "Error: Your account cannot be created at this time. Please try again later.";
         }
        
         $response = $response->withHeader('Content-Type','application/json');
         $response = $response->withStatus(201);
         $response = $response->withJson($data);
         
         return $response;
      } catch (QueryException $e) {
        $this['logger']->error("DataBase Error.<br/>" . $e->getMessage());
      } catch (Exception $e) {
        $this['logger']->error("General Error.<br/>" . $e->getMessage());
      } finally {
        // Destroy the database connection
        $conn = null;
      }
   });

   /**
      * This method gets a user into the database.
      * @param string $user - username
      * @param string $pass - password
      */
      $app->get('/login', function (Request $request, Response $response) {
      // Gets username and password
         $user = $request->getParam("email");
         $pass = $request->getParam("password");

         // Gets the database connection
         // $conn = PDOConnection::getConnection();

         try {
            // Gets the user into the database
         //    $sql = "SELECT * FROM users WHERE username=:user";
         //     $stmt = $conn->prepare($sql);
         // $stmt->bindParam(":user", $user);
         // $stmt->execute();
         // $query = $stmt->fetchObject();
         $user = User::where('username', $user)->first();

         // If user exist
         if ($user) {
            // If password is correct
            if (password_verify($pass, $user->password)) {
            // Create a new resource
            $data['user'] = $user;
            $data['token'] = JWTAuth::getToken($user->id_user, $user->username);
          } else {
               // Password wrong
            $data['status'] = "Error: The password you have entered is wrong.";
            }
         } else {
            // Username wrong
          $data['status'] = "Error: The user specified does not exist.";
         }

        // Return the result
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
