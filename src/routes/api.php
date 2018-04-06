<?php

   use App\Models\User;
   use App\Models\UserVehicle;
   use App\Models\Signature;

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
         $mobile = '91' . $request->getParam("mobile");

         // Account details
         $apiKey = urlencode('qh6V1C/XaDs-YMIw6wGFvCV45uJKz0DAFoij5tiVLO');
   
         // Message details
         $numbers = array($mobile);
         $sender = urlencode('TXTLCL');
         $message = rawurlencode('Thanks for joining Vyasanmukt Uttar Pradesh abhiyan.Your account has been successfully created.Your registeration ID is '. $result->guid);
 
         // Prepare data for POST request
         $data2 = array('apikey' => $apiKey, 'numbers' => $mobile, "sender" => $sender, "message" => $message);
 
         // Send the POST request with cURL
         $ch = curl_init('https://api.textlocal.in/send/');
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $data2);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $response2 = curl_exec($ch);
         curl_close($ch);
   
         // Process your response here
         $data['mobile'] = $response2;

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

         try {
            // Gets the user into the database
            $user = User::where('username', $user)->orWhere('mobile', $user)->first();

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

      /**
       * This method cheks the token.
       */
      $app->get('/verify', function (Request $request, Response $response) {
         // Gets the token of the header.
         $token = str_replace('Bearer ', '', $request->getServerParams()['HTTP_AUTHORIZATION']);
         
         // Verify the token.
         $result = JWTAuth::verifyToken($token);
         
         // Return the result
         $data['status'] = $result;
         $response = $response->withHeader('Content-Type','application/json');
         $response = $response->withStatus(200);
         $response = $response->withJson($data);
         return $response;
      });

      /**
      * This method publish short text messages of no more than 120 characters
      * @param string $quote - The text of post
      * @param int $id - The user id
      */
      $app->post('/vehicle/create', function (Request $request, Response $response) {
         // Gets quote and user id
         $id = $request->getParam('id');
         $vehicle = $request->getParam('vehicle');
         $male = $request->getParam('male');
         $female = $request->getParam('female');
         $count = $request->getParam('count');

         // Gets the database connection
         try {
            // Gets the user into the database
            $user = User::where('id_user', $id)->first();
         
            // If user exist
            if ($user) {
        
               $result = UserVehicle::create([
                  'id_user' => $id,
                  'id_vehicle' => $vehicle,
                  'total_female' => $female,
                  'total_male' => $male,
                  'total_vehicle' => $count
               ]);

               $data['status'] = $result;
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
            // $conn = null;
         }
      });

      /**
      * This method publish short text messages of no more than 120 characters
      * @param string $quote - The text of post
      * @param int $id - The user id
      */
      $app->post('/signature/create', function (Request $request, Response $response) {
         // Gets quote and user id
         $id = $request->getParam('id');

         $newSignature = array(
            'id_district' => $request->getParam('district'),
            'file_name' => $request->getParam('file_name'),
            'from_' => $request->getParam('from'),
            'to_' => $request->getParam('to'),
            'total_sheet' => $request->getParam('sheet'),
            'contact_name' => $request->getParam('name'),
            'contact_mobile' => $request->getParam('mobile'),
            'total_college' => $request->getParam('college'),
            'total_student' => $request->getParam('student'),
            'total_teacher' => $request->getParam('teacher'),
            'total_general' => $request->getParam('general'),
            'total_representative' => $request->getParam('representative'),
            'total_exoffice' => $request->getParam('exoffice'));

         // Gets the database connection
         try {
            // Gets the user into the database
            $user = User::where('id_user', $id)->first();
         
            // If user exist
            if ($user) {
        
               $result = Signature::create($newSignature);
               $data['status'] = $result;

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
            // $conn = null;
         }
      });
   });
?>