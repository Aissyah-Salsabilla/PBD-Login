<?php

namespace ProgrammerZamanNow\Belajar\PHP\MVC\Service;

use PHPUnit\Framework\TestCase;
use ProgrammerZamanNow\Belajar\PHP\MVC\Config\Database;
use ProgrammerZamanNow\Belajar\PHP\MVC\Exception\ValidationException;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserLoginRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterRequest;
use ProgrammerZamanNow\Belajar\PHP\MVC\Model\UserRegisterResponse;
use ProgrammerZamanNow\Belajar\PHP\MVC\Respository\UserRepository;

class UserServiceTest extends TestCase
{
    private UserService $userService;
    private UserRepository $userRepository;

    protected function setUp():void
    {
        $connection = Database::getCOnnection();
        $this->userRepository = new UserRepository($connection);
        $this->userService = new UserService($this->userRepository);

        $this->userRepository->deleteAll();
    }
    public function testRegisterSuccess()
    {
        $request = new UserRegisterRequest();
        $request->id = "eko";
        $request->name = "Eko";
        $request->password = "rahasia";

        $response = $this->userService->register($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertEquals($request->name, $response->user->name);
        self::assertNotEquals($request->password, $response->user->pasword);

        self::assertTrue(password_verify($request->id, $response->user->id));
    }

    public function testRegisterFailed()
    {
        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "";
        $request->name = "";
        $request->password = "";

        $this->userService->register($request);

    }

    public function testRegisterDuplicate()
    {
        $user = new User();
        $user->id = "eko";
        $user->name = "Eko";
        $user->password = "rahasia";

        $this->userRepository->save($user);

        $this->expectException(ValidationException::class);

        $request = new UserRegisterRequest();
        $request->id = "eko";
        $request->name = "Eko";
        $request->password = "rahasia";

        $this->userService->register($request);
    }

    public function testLoginNotFound()
    {
       $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "eko";
        $request->password = "eko";

        $this->userService->login($request);

    }

    public function testLoginWrongPassword()
    {
        $user = new User();
        $user->id = "eko";
        $user->name = "Eko";
        $user->password = password_hash("eko", PASSWORD_BCRYPT);
        $this->expectException(ValidationException::class);
        $request = new UserLoginRequest();
        $request->id = "eko";
        $request->password = "salah";

        $this->userService->login($request);


    }

    public function testLoginSuccess()
    {
        $user = new User();
        $user->id = "eko";
        $user->name = "Eko";
        $user->password = password_hash("eko", PASSWORD_BCRYPT);

        $this->expectException(ValidationException::class);

        $request = new UserLoginRequest();
        $request->id = "eko";
        $request->password = "eko";

        $response =$this->userService->login($request);

        self::assertEquals($request->id, $response->user->id);
        self::assertTrue(password_verify($request->password, $response->user->password));



    }

}
