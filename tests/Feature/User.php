<?php

use App\Http\Controllers\AuthController;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutExceptionHandling;
use Symfony\Component\HttpFoundation\Response;
use function Tests\createGroup;
use function Tests\createUser;
use function Tests\login;
use function Tests\register;

beforeEach(function () {
    $this->userGroup = createGroup();
    $this->user = createUser($this->userGroup);
    $this->adminGroup = createGroup();
    $this->admin = createUser($this->adminGroup, true);
    Cache::flush();
});

/*
    AuthController::isAdmin
*/

test("L'utilisateur n'est pas administrateur", function () {
    actingAs($this->user)
        ->assertFalse(
            AuthController::isAdmin($this->user)
        );
});

test("L'administrateur est administrateur", function () {
    actingAs($this->admin)
        ->assertTrue(
            AuthController::isAdmin($this->admin)
        );
});

/*
    AuthController::login
*/

test("L'utilisateur peut se connecter", function () {
    login($this->user->email, 'admin')
        ->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJsonStructure([
            'message',
            'data',
        ]);
});

test("L'utilisateur ne peut pas se connecter, mauvais mot de passe", function () {
    login($this->user->email, 'test')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonStructure([
            'message',
        ]);
});

test("L'utilisateur ne peut pas se connecter, mauvaise adresse mail", function () {
    login('test@test.fr', 'admin')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonStructure([
            'message',
        ]);
});

test("L'administrateur peut se connecter", function () {
    login($this->admin->email, 'admin')
        ->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJsonStructure([
            'message',
            'data',
        ]);
});

test("L'administrateur ne peut pas se connecter, mauvais mot de passe", function () {
    login($this->admin->email, 'test')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonStructure([
            'message',
        ]);
});

test("L'administrateur ne peut pas se connecter, mauvaise adresse mail", function () {
    login('test@test.fr', 'admin')
        ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ->assertJsonStructure([
            'message',
        ]);
});

/*
    AuthController::logout
*/

//TODO : test logout

/*
    AuthController::user
*/

test("L'utilisateur peut consulter son profil", function () {
    actingAs($this->user)
        ->get("/api/user")
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(9);
});

test("L'administrateur peut consulter son profil", function () {
    actingAs($this->admin)
        ->get("/api/user")
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonCount(9);
});

/*
    AuthController::index
*/

test("L'utilisateur ne peut pas consulter la liste des utilisateurs", function () {
    actingAs($this->user)
        ->get("/api/user/list")
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut consulter la liste des utilisateurs", function () {
    actingAs($this->admin)
        ->get("/api/user/list")
        ->assertStatus(Response::HTTP_OK);
});

/*
    AuthController::show
*/

test("L'utilisateur ne peut pas consulter un utilisateur", function () {
    actingAs($this->user)
        ->get("/api/user/".$this->admin->id)
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut consulter un utilisateur", function () {
    actingAs($this->admin)
        ->get("/api/user/".$this->user->id)
        ->assertStatus(Response::HTTP_OK);
});

/*
    AuthController::register
*/

test("L'utilisateur ne peut pas inscrire un utilisateur", function () {
    register(
        $this->user,
        $this->userGroup->id,
        'registertest',
        'registertest@test.fr',
        'admin',
        'admin',
        false
    )
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut inscrire un utilisateur", function () {
    register(
        $this->admin,
        $this->userGroup->id,
        'registertest',
        'registertest@test.fr',
        'admin',
        'admin',
        false
    )
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonStructure([
            'message',
            'data',
        ])
        ->assertJsonFragment([
            'name' => 'registertest',
            'email' => 'registertest@test.fr',
            'is_admin' => false,
            'is_active' => true,
        ]);
});

test("L'administrateur ne peut pas inscrire un utilisateur, aucun identifiant de groupe n'a été fourni", function () {
    register(
        user: $this->admin,
        name: 'registertest',
        email: 'registertest@test.fr',
        password: 'admin',
        password_confirmation: 'admin',
        is_admin: false
    )
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test("L'administrateur ne peut pas inscrire un utilisateur, le mot de passe et la confirmation ne correspondent pas", function () {
    register(
        user: $this->admin,
        group_id: $this->userGroup->id,
        name: 'registertest',
        email: 'registertest@test.fr',
        password: 'admin',
        password_confirmation: 'admin2',
        is_admin: false
    )
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test("L'administrateur ne peut pas inscrire un utilisateur, l'adresse mail est déjà utilisée", function () {
    register(
        user: $this->admin,
        group_id: $this->userGroup->id,
        name: 'registertest',
        email: $this->user->email,
        password: 'admin',
        password_confirmation: 'admin',
        is_admin: false
    )
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test("L'administrateur ne peut pas inscrire un utilisateur, l'adresse mail n'est pas conforme", function () {
    register(
        user: $this->admin,
        group_id: $this->userGroup->id,
        name: 'registertest',
        email: 'yrdy',
        password: 'admin',
        password_confirmation: 'admin',
        is_admin: false
    )
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

//TODO : test des mots de passe

/*
    AuthController::update
*/

test("L'utilisateur ne peut pas modifier un utilisateur", function () {
    actingAs($this->user)
        ->put("/api/user/".$this->admin->id, [
            'name' => 'test',
        ])
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut modifier un utilisateur", function () {
    actingAs($this->admin)
        ->put("/api/user/".$this->user->id, [
            'name' => 'test',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data',
        ])
        ->assertJsonFragment([
            'name' => 'test',
        ]);
});

test("L'administrateur ne peut pas modifier un utilisateur, l'adresse mail est déjà utilisée", function () {
    actingAs($this->admin)
        ->put("/api/user/".$this->user->id, [
            'email' => $this->user->email,
        ])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test("L'administrateur ne peut pas modifier un utilisateur, l'adresse mail n'est pas conforme", function () {
    actingAs($this->admin)
        ->put("/api/user/".$this->user->id, [
            'email' => 'yrdy',
        ])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

/*
    AuthController::updateCurrent
*/

test("L'utilisateur peut modifier son propre compte", function () {
    actingAs($this->user)
        ->put("/api/user/updatecurrent", [
            'name' => 'test',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonStructure([
            'message',
            'data',
        ])
        ->assertJsonFragment([
            'name' => 'test',
        ]);
});

test("L'utilisateur ne peut pas modifier son propre compte, l'adresse mail est déjà utilisée", function () {
    actingAs($this->user)
        ->put("/api/user/updatecurrent", [
            'email' => $this->admin->email,
        ])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

test("L'utilisateur ne peut pas modifier son propre compte, l'adresse mail n'est pas conforme", function () {
    actingAs($this->user)
        ->put("/api/user/updatecurrent", [
            'email' => 'yrdy',
        ])
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
});

/*
    AuthController::indexWithTrashed
*/

test("L'utilisateur ne peut pas lister les utilisateurs supprimés", function () {
    actingAs($this->user)
        ->get("/api/user/trashed")
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut lister les utilisateurs supprimés", function () {
    actingAs($this->admin)
        ->get("/api/user/trashed")
        ->assertStatus(Response::HTTP_OK);
});

/*
    AuthController::trash
*/

test("L'utilisateur ne peut pas supprimer un utilisateur", function () {
    actingAs($this->user)
        ->delete("/api/user/trash/".$this->admin->id)
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut supprimer un utilisateur", function () {
    actingAs($this->admin)
        ->delete("/api/user/trash/".$this->user->id)
        ->assertStatus(Response::HTTP_OK);
});

/*
    AuthController::destroy
*/

test("L'utilisateur ne peut pas supprimer définitivement un utilisateur", function () {
    actingAs($this->user)
        ->delete("/api/user/destroy/".$this->admin->id)
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut supprimer définitivement un utilisateur", function () {
    actingAs($this->admin)
        ->delete("/api/user/destroy/".$this->user->id)
        ->assertStatus(Response::HTTP_OK);
});

/*
    AuthController::restore
*/

test("L'utilisateur ne peut pas restaurer un utilisateur", function () {
    actingAs($this->user)
        ->post("/api/user/restore/".$this->admin->id)
        ->assertStatus(Response::HTTP_UNAUTHORIZED);
});

test("L'administrateur peut restaurer un utilisateur", function () {
    withoutExceptionHandling();
    $userId = $this->user->id;
    actingAs($this->admin)
        ->delete("/api/user/trash/".$userId)
        ->assertStatus(Response::HTTP_OK);

    actingAs($this->admin)
        ->post("/api/user/restore/".$userId)
        ->assertStatus(Response::HTTP_OK);
});
