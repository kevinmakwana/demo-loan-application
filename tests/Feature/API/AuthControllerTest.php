<?php

namespace Tests\Feature\API;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function setUp() :void
    {
        parent::setUp();

        // create a user
        User::factory()->create([
            'email' => 'testuser@mailinator.com',
            'password' => Hash::make('testpassword')
        ]);

    }

    public function test_show_validation_error_when_all_fields_empty_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => '',
            'email' => '',
            'password' => '',
            'confirm_password' => ''
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'name',
            'email',
            'password',
            'confirm_password'
        ]]);
    }

    public function test_show_validation_error_when_name_field_empty_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => '',
            'email' => 'jon@mailinator.com',
            'password' => '123456789',
            'confirm_password' => '123456789'
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'name',
        ]])
        ->assertJsonPath('message.name.0','The name field is required.');
    }

    public function test_show_validation_error_when_email_field_empty_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => '',
            'password' => '123456789',
            'confirm_password' => '123456789'
        ],['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'email',
        ]])
        ->assertJsonPath('message.email.0','The email field is required.');
    }

    public function test_show_validation_error_when_password_field_empty_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'jonDoe@mailinator.com',
            'password' => '',
            'confirm_password' => '123456789'
        ],['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'password',
        ]])
        ->assertJsonPath('message.password.0','The password field is required.');
    }

    public function test_show_validation_error_when_confirm_password_field_empty_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'jonDoe@mailinator.com',
            'password' => '123456789',
            'confirm_password' => ''
        ],['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'confirm_password',
        ]])
        ->assertJsonPath('message.confirm_password.0','The confirm password field is required.');
    }

    public function test_show_validation_error_when_email_is_not_proper_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'jon.doe.com',
            'password' => '1234567890',
            'confirm_password' => '1234567890'
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'email'
        ]])
        ->assertJsonPath('message.email.0','The email must be a valid email address.');
    }

    public function test_show_validation_error_when_email_already_exists_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'testuser@mailinator.com',
            'password' => '1234567890',
            'confirm_password' => '1234567890'
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'email'
        ]])
        ->assertJsonPath('message.email.0','The email has already been taken.');
    }

    public function test_show_validation_error_when_password_and_confirm_password_are_not_same_at_register()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'test1user@mailinator.com',
            'password' => '123456789',
            'confirm_password' => '1234567890'
        ],['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'confirm_password'
        ]])
        ->assertJsonPath('message.confirm_password.0','The confirm password and password must match.');
    }

    public function test_return_user_and_access_token_after_successful_registration()
    {
        $response = $this->json('POST',config('app.url').'/api/register', [
            'name' => 'jonDoe',
            'email' => 'test1user@mailinator.com',
            'password' => '123456789',
            'confirm_password' => '123456789'
        ],['Accept' => 'application/json']);

        $response->assertStatus(200)
        ->assertJsonStructure(['data' => [
            'id',
            'name',
            'email',
            'token'
        ]])
        ->assertJson(['message' => 'Your account has been created successfully.']);
    }


    public function test_show_validation_error_when_both_fields_empty_at_login()
    {
        $response = $this->json('POST',config('app.url').'/api/login', [
            'email' => '',
            'password' => ''
        ],['Accept' => 'application/json']);

        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'email',
            'password'
        ]]);
    }

    public function test_show_validation_error_when_credential_do_not_match()
    {
        $response = $this->json('POST', config('app.url').'/api/login', [
            'email' => 'test@test.com',
            'password' => 'abcdabcd'
        ],['Accept' => 'application/json']);

        $response->assertStatus(401)
        ->assertJson(['message' => 'Credentials do not match.']);
    }

    public function test_show_validation_error_when_email_is_not_proper_at_login()
    {
        $response = $this->json('POST', config('app.url').'/api/login', [
            'email' => 'test.test.com',
            'password' => 'abcdabcd'
        ],['Accept' => 'application/json']);
        
        $response->assertStatus(422)
        ->assertJsonStructure(['message' => [
            'email'
        ]])
        ->assertJsonPath('message.email.0','The email must be a valid email address.');
    }

    public function test_return_user_and_access_token_after_successful_login()
    {
        $response = $this->json('POST', config('app.url').'/api/login', [
            'email' =>'testuser@mailinator.com',
            'password' => 'testpassword',
        ],['Accept' => 'application/json']);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Login Successfully.'])
            ->assertJsonStructure(['data'=>[
                'id',
                'name',
                'email',
                'is_admin',
                'token'
            ]]);
    }

    public function test_non_authenticated_user_cannot_logout()
    {
        $response = $this->json('POST', config('app.url').'/api/logout', [],['Accept' => 'application/json']);
        
        $response->assertStatus(401)
        ->assertJson(['message' => 'Unauthorized.']);
    }

    public function test_authenticated_user_can_logout()
    {
        Sanctum::actingAs(
            User::first(),
        );

        $response = $this->json('POST', config('app.url').'/api/logout', [], ['Accept' => 'application/json']);

        $response->assertStatus(200)
        ->assertJson(['message' => 'Logout successfully.']);
    }
}
