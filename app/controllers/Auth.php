<?php
//for user login and logout

class Auth extends Controller
{
    private $db;
    public function __construct()
    {
        $this->model('UserModel');
        $this->db = new Database();
    }

    public function formRegister()
    {
        if (
            $_SERVER['REQUEST_METHOD'] == 'POST' &&
            isset($_POST['email_check']) &&
            $_POST['email_check'] == 1
        ) {
            $email = $_POST['email'];
            // call columnFilter Method from Database.php
            $isUserExist = $this->db->columnFilter('users', 'email', $email);
            if ($isUserExist) {
                echo 'Sorry! email has already taken. Please try another.';
            }
        }
    }

    public function register()
{
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $isUserExist = $this->db->columnFilter('users', 'email', $email);

        if ($isUserExist) {
            setMessage('error', 'This email is already registered!');
            redirect('pages/register');
        } else {
            $validation = new UserValidator($_POST);
            $data = $validation->validateForm();

            if (count($data) > 0) {
                $this->view('pages/register', $data);
            } else {
                $name = $_POST['name'];
                $password = $_POST['password'];

                $profile_image = 'default_profile.jpg';
                $token = bin2hex(random_bytes(50));
                $password = base64_encode($password); // Note: base64 is NOT secure for real passwords

                $user = new UserModel();
                $user->setName($name);
                $user->setEmail($email);
                $user->setPassword($password);
                $user->setToken($token);
                $user->setProfileImage($profile_image);
                $user->setIsLogin(0);
                $user->setIsActive(0);
                $user->setIsConfirmed(0);
                $user->setDate(date('Y-m-d H:i:s'));

                $userCreated = $this->db->create('users', $user->toArray());

                if ($userCreated) {
                    $mail = new Mail();
                    $verify_token = URLROOT . '/auth/verify/' . $token;
                    $mail->verifyMail($email, $name, $verify_token);

                    setMessage('success', 'Please check your Mail box!');
                    redirect('pages/login');
                } else {
                    setMessage('error', 'Something went wrong while creating your account.');
                    redirect('pages/register');
                }
            }
        }
    } else {
        // SHOW THE REGISTRATION FORM FOR GET REQUEST
        $this->view('pages/register');
    }
}


    // Corrected verify method with optional token parameter and check
    public function verify($token = null)
    {
        // echo 'Verify Page';
        if (!$token) {
            setMessage('error', 'Verification token missing!');
            redirect('');
            return;
        }

        $user = $this->db->columnFilter('users', 'token', $token);

        if ($user) {
            $success = $this->db->setLogin($user[0]['id']);

            if ($success) {
                setMessage(
                    'success',
                    'Successfully Verified. Please log in!'
                );
            } else {
                setMessage('error', 'Fail to Verify. Please try again!');
            }
        } else {
            setMessage('error', 'Incorrect Token. Please try again!');
        }

        redirect('');
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['email']) && isset($_POST['password'])) {
                $email = $_POST['email'];
                $password = base64_encode($_POST['password']);

                $isLogin = $this->db->loginCheck($email, $password);

                if ($isLogin) {
                    setMessage('id', base64_encode($isLogin['id']));
                    $id = $isLogin['id'];
                    $setLogin = $this->db->setLogin($id);
                    redirect('pages/dashboard');
                } else {
                    setMessage('error', 'Login Fail!');
                    redirect('pages/login');
                }
            }
        }
    }

    function logout($id)
    {
        $this->db->unsetLogin($id);
        redirect('pages/login');
    }
}
