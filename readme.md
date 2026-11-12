Laravel 13 Secure API: Role-Based Access Control (RBAC)

Laboratory activity for Integrative Programming & Technologies II, Module 5, Lesson 2 (Authorization and Role-Based Access Control).

This project extends the Sanctum-secured API with two custom middleware classes that restrict endpoints by user role.

How it works
Authentication: Laravel Sanctum (auth:sanctum) checks the Bearer token.
Authorization: two custom middleware classes check the user's role column.
CheckRole accepts one or more allowed roles, for example role:admin,student.
AdminMiddleware allows admins only.
Both middleware return JSON 401 when no user is authenticated and JSON 403 when the role is not allowed.
Aliases role and admin are registered in bootstrap/app.php.
The users.role column is a string with a default of student. Public registration never accepts a role field, so new accounts are always students. The admin role is assigned only through trusted server-side code (Tinker).
RBAC routes
Endpoint	Method	Middleware	Admin	Student	No token
/api/profile	GET	auth:sanctum	200	200	401
/api/students	GET	auth:sanctum, role:admin,student	200	200	401
/api/admin/dashboard	GET	auth:sanctum, admin	200	403	401
/api/student/dashboard	GET	auth:sanctum, role:student	403	200	401

Public routes: POST /api/register, POST /api/login. Protected: POST /api/logout.

/api/students returns a small fixed JSON array; no student table is used.

Local setup

Requirements: PHP 8.4+, Composer, MySQL (XAMPP/WAMP), Postman.

bash
composer install
cp .env.example .env
php artisan key:generate

Edit .env with your own MySQL database name and credentials, then:

bash
php artisan migrate
php artisan optimize:clear
php artisan route:list --path=api -v
php artisan serve

The API runs at http://127.0.0.1:8000/api.

Create the test accounts

Do not use migrate:fresh on a database with data you want to keep. Create or update one admin and one student in Tinker (php artisan tinker):

php
use Illuminate\Support\Facades\Hash;
use App\Models\User;

User::updateOrCreate(
    ['email' => 'admin@example.com'],
    ['name' => 'Admin', 'password' => Hash::make('choose-a-password')]
);
User::where('email', 'admin@example.com')->update(['role' => 'admin']);

User::updateOrCreate(
    ['email' => 'student@example.com'],
    ['name' => 'Student', 'password' => Hash::make('choose-a-password')]
);

Choose your own passwords. Never commit passwords or tokens.

Testing with Postman

Add the header Accept: application/json to every request. For protected routes use Authorization → Bearer Token with the token from /api/login. For the no-token test, set Authorization to No Auth.

Login request body (POST /api/login):

json
{
  "email": "admin@example.com",
  "password": "your-password"
}

Note: this project's login returns the token as access_token (with token_type: Bearer) instead of a field named token. It authenticates successfully and returns 200.

Test	Request	Token used	Expected	Screenshot
1	POST /api/login	None (admin credentials)	200	screenshots/01-admin-login.png
2	GET /api/admin/dashboard	Admin	200	screenshots/02-admin-allowed.png
3	GET /api/student/dashboard	Admin	403	screenshots/03-admin-denied.png
4	POST /api/login	None (student credentials)	200	screenshots/04-student-login.png
5	GET /api/student/dashboard	Student	200	screenshots/05-student-allowed.png
6	GET /api/admin/dashboard	Student	403	screenshots/06-student-denied.png
7	GET /api/students	Student	200	screenshots/07-shared-route.png
8	GET /api/admin/dashboard	No token	401	screenshots/08-no-token.png

Tokens and passwords are hidden in all screenshots.

Troubleshooting
Problem	Check
401 Unauthenticated	Bearer token is present and valid; Accept: application/json is set
403 Forbidden unexpectedly	The user's role in the database and the role listed on the route
404 Not Found	Route appears in php artisan route:list --path=api and the URL includes /api
Target class [role] does not exist	Aliases in bootstrap/app.php; run php artisan optimize:clear
API returns HTML	Add Accept: application/json in Postman
Reflection

What is the difference between authentication and authorization? Authentication verifies who the user is, in this project by checking the Sanctum Bearer token. Authorization decides what that authenticated user is allowed to do, here by checking the user's role against the roles a route permits.

Why does a student receive 403 on the admin dashboard, but a request without a token receives 401? A request without a token cannot be authenticated, so the server does not know who is asking and returns 401 Unauthenticated. A student with a valid token is authenticated, but the student role is not allowed on the admin route, so the server returns 403 Forbidden.
