<?php

namespace App\Http\Controllers;

use App\Status;
use App\User;
use App\UserType;
use App\UserUserType;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class UserController extends MasterBaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct(new User());
    }

    /*
    |--------------------------------------------------------------------------
    | Api สมัครสมาชิก
    |--------------------------------------------------------------------------
     */
    public function register(Request $request)
    {
        // validator
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:user',
            'username' => 'required|unique:user',
            'password' => 'required',
            'fullname' => 'required',
            'gender_id' => 'required',
        ]);

        if ($validator->fails()) {

            $errors = $validator->errors();

            return $this->responseRequestError($errors);

        } else {

            $user = $this->model;
            $user->setData($request->request);
            $user->password = Hash::make($request->password);

            if ($user->save()) {

                $user_user_type = new UserUserType();
                $user_user_type->user_id = $user->id;
                $user_user_type->user_type_id = $request->user_type_id;

                if ($user_user_type->save()) {
                    return $this->showResponseAuthen($user);
                } else {
                    return $this->responseRequestError('ไม่สามารถสมัครสมาชิกได้');
                }

            } else {
                return $this->responseRequestError('ไม่สามารถสมัครสมาชิกได้');
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Api เข้าสู่ระบบ
    |--------------------------------------------------------------------------
     */
    public function login(Request $request)
    {
        $user = $this->model::where('username', $request->username)
            ->where('status_id', Status::$ACTIVE)
            ->first();

        if (!empty($user) && Hash::check($request->password, $user->password)) {
            return $this->showResponseAuthen($user);
        } else {
            return $this->responseRequestError("Username or password ของท่านไม่ถูกต้อง");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | ตัวเข้ารหัส JWT
    |--------------------------------------------------------------------------
     */
    protected function jwt($user)
    {
        $user = $user->toArray();
        unset($user['facebook_id']);
        unset($user['google_id']);
        $payload = [
            'iss' => "lumen-jwt", // Issuer of the token
            'sub' => json_encode($user), // Subject of the token
            'iat' => time(), // Time when JWT was issued.
            'exp' => time() + env('JWT_EXPIRE_HOUR') * 60 * 60, // Expiration time
        ];

        return JWT::encode($payload, env('JWT_SECRET'));
    }

    /*
    |--------------------------------------------------------------------------
    | Api ดูข้อมูลผู้ใช่งาน
    |--------------------------------------------------------------------------
     */
    public function getData($user_id)
    {
        $data = $this->model::join('user_user_type', 'user_user_type.user_id', 'user.id')
            ->where('user.id', $user_id)
            ->where('user.status_id', Status::$ACTIVE)
            ->select('user.*', 'user_user_type.user_type_id as user_type_id')
            ->get();

        return $this->responseRequestSuccess($data);
    }

    /*
    |--------------------------------------------------------------------------
    | Api แก้ไขข้อมูลผู้ใช่งาน
    |--------------------------------------------------------------------------
     */
    public function updateData(Request $request, $user_id)
    {

        // ดึงข้อมูล User มาเพื่อที่จะแก้ไข
        $user = $this->model::where('status_id', Status::$ACTIVE)
            ->find($user_id);
        $validate_rule = [];

        if (!empty($user)) {

            // ตรวจสอบ email ซ้ำไหม
            if ($user->email !== $request->email) {
                $validate_rule['email'] = 'required|email|unique:user';
            }

            // ตรวจสอบ username ซ้ำไหม
            if ($user->username !== $request->username) {
                $validate_rule['username'] = 'required|unique:user';
            }

            $validator = \Validator::make($request->all(), $validate_rule);

            if ($validator->fails()) {

                $errors = $validator->errors();

                return $this->responseRequestError($errors);
            } else {

                $user->setData($request->request);

                if ($user->save()) {

                    $user_user_type = UserUserType::where('user_id', $user->id)
                        ->update(['user_type_id' => $request->user_type_id]);

                    return $this->responseRequestSuccess($user);

                } else {
                    return $this->responseRequestError('ไม่สามรถแก้ไขข้อมูลได้');
                }
            }
        } else {
            return $this->responseRequestError('ไม่พบข้อมูลสมาชิก');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Api อัพโหลดรูปสำหรับ Profile
    |--------------------------------------------------------------------------
     */
    public function uploadImageProfile(Request $request, $user_id)
    {
        $user = $this->model::where('id', $user_id)
            ->where('status_id', Status::$ACTIVE)
            ->first();
        // check Upload แล้วเก็บ path image
        $path = $this->saveImage($request->profile_image, 'user');
        $old_image = $user->profile_image;

        // check และ บันทึก path รูปลองฐานข้อมูล แล้ว response ข้อมูลออกมา
        if ($path) {

            $user->profile_image = $path;

            if ($user->save()) {

                // check ถ้า profile_image ไม่เท่ากับ Null ให้ลบรูปก่อนแล้วไป upload รูปใหม่
                if ($old_image != null) {
                    $this->removeOldImage($old_image);
                }

                return $this->responseRequestSuccess($user);
            } else {

                if ($old_image != null) {
                    $this->removeOldImage($path);
                }

                return $this->responseRequestError('บันทึก File');
            }
        } else {
            return $this->responseRequestError('File รูปมีนี้ไม่สามารถอัพโหลดได้ กรุณาตรวจสอบอีกครั้ง');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Api เปลี่ยนรหัสผ่าน
    |--------------------------------------------------------------------------
     */
    public function changePassword(Request $request, $user_id)
    {
        $user = $this->model::where('status_id', Status::$ACTIVE)
            ->find($user_id);
        $password = null;

        if (!empty($user) && Hash::check($request->password, $user->password)) {

            if ($request->new_password === $request->confirm_password) {

                $password = Hash::make($request->new_password);
                $this->model::where('status_id', Status::$ACTIVE)
                    ->where('id', $user_id)
                    ->update(['password' => $password]);

                return $this->responseRequestSuccess($user);
            } else {
                return $this->responseRequestError('รหัสผ่านใหม่ของคุณไม่ตรงกัน กรุณากรอกรหัสผ่านใหม่ให้ตรงกัน');
            }
        } else {
            return $this->responseRequestError('รหัสผ่านเดิมของคุณไม่ถูกต้อง');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Api ลืม password
    |--------------------------------------------------------------------------
     */
    public function forgotPassword(Request $request)
    {
        $user = $this->model::where('email', $request->email)
            ->first();

        if (!empty($user)) {

            $raw_rpk = 'reset_password|' . $user->email . '|' . $user->id;
            $rpk = Crypt::encryptString($raw_rpk);
            $template_html = 'mail.forgot_password';
            $template_data = [
                'url_reset_password' => url('/user/reset_password/' . $rpk),
            ];

            Mail::send($template_html, $template_data, function ($msg) use ($user) {
                $msg->subject('ลืมรหัสผ่าน === Sampran E-Commerce');
                $msg->to([$user->email]);
                $msg->from('agriconnect.mulberrysoft@gmail.com', 'Mulberrysoft');
            });

            return $this->responseRequestSuccess('รหัสผ่านของท่านส่งไปที่อีเมลเรียบร้อยแล้ว');
        } else {
            return $this->responseRequestError('ไม่พบอีเมลนี้ในระบบ กรุณาตรวจสอบอีเมลของคุณใหม่อีกครั้ง');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Api reset password ใหม่
    |--------------------------------------------------------------------------
     */
    public function resetPassword($rpk)
    {
        try {

            $rpk_raw = Crypt::decryptString($rpk);
            $rpk_arr = explode('|', $rpk_raw);

            if (count($rpk_arr) == 3) {

                $email = $rpk_arr[1];
                $user_id = $rpk_arr[2];
                $user = $this->model::where('email', $email)
                    ->find($user_id);

                if (!empty($user)) {

                    $temp_password = $this->strRandom();
                    $user->password = Hash::make($temp_password);

                    if ($user->save()) {

                        $template_html = 'mail.reset_password';
                        $template_data = [
                            'temp_password' => $temp_password,
                        ];

                        Mail::send($template_html, $template_data, function ($msg) use ($user) {
                            $msg->subject('รหัสผ่านชั่วคราว  === Sampran E-Commerce');
                            $msg->to([$user->email]);
                            $msg->from('agriconnect.mulberrysoft@gmail.com', 'Mulberrysoft');
                        });

                        return 'ระบบได้ทำการเปลี่ยนรหัสผ่านชั่วคราวให้คุณแล้ว กรุณาตรวจสอบอีเมลเพื่อนำรหัสผ่านชั่วคราวมาใช้ในการเข้าสู่ระบบบัญชีของคุณ';
                    } else {
                        return 'เกิดข้อผิดพลาด ไม่สามารถอัพเดทข้อมูลได้';
                    }
                } else {
                    return 'ไม่พบข้อมูล';
                }
            } else {
                return 'ไม่พบข้อมูล';
            }
        } catch (DecryptException $e) {
            return 'ไม่พบข้อมูล';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | function สำหรับ Random String
    |--------------------------------------------------------------------------
     */
    protected function strRandom($length = 6)
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }

    /*
    |--------------------------------------------------------------------------
    | function สำหรับ Login Google
    |--------------------------------------------------------------------------
     */
    public function googleAuthen(Request $request)
    {
        $request->new_token = null;
        $response = null;
        $user = User::where('google_id', $request->google_id)
            ->where('status_id', Status::$ACTIVE)
            ->first();

        if (!empty($user)) {

            $response = $this->showResponseAuthen($user);

        } else {

            $user = User::where('email', $request->email)
                ->where('status_id', Status::$ACTIVE)
                ->first();

            if (!empty($user)) {

                $user->google_id = $request->google_id;

                if ($user->save()) {

                    $response = $this->showResponseAuthen($user);

                } else {

                    $response = $this->responseRequestError("ไม่สามารถเข้าสู้ระบบได้");

                }

            } else {

                $user = new User();
                $user->username = $this->strRandom() . '-' . date('YmdHis');
                $user->fullname = $request->fullname;
                $user->email = $request->email;
                $user->google_id = $request->google_id;
                $user->password = Hash::make($this->strRandom());
                $user->profile_image = $request->profile_image;

                if ($user->save()) {

                    $user_user_type = new UserUserType();
                    $user_user_type->user_id = $user->id;
                    $user_user_type->user_type_id = $request->user_type_id;

                    if ($user_user_type->save()) {

                        $response = $this->showResponseAuthen($user);

                    } else {

                        $response = $this->responseRequestError("ไม่สามารถเพิ่มประเภทผู้ใช้งานได้");

                    }

                } else {

                    $response = $this->responseRequestError("ไม่สามารถสมัครสมาชิกได้");

                }
            }
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | function สำหรับ Login Facebook
    |--------------------------------------------------------------------------
     */
    public function facebookAuthen(Request $request)
    {
        $request->new_token = null;
        $response = null;
        $user = User::where('facebook_id', $request->facebook_id)
            ->where('status_id', Status::$ACTIVE)
            ->first();

        if (!empty($user)) {

            $response = $this->showResponseAuthen($user);

        } else {

            $user = User::where('email', $request->email)
                ->where('status_id', Status::$ACTIVE)
                ->first();

            if (!empty($user)) {

                $user->facebook_id = $request->facebook_id;

                if ($user->save()) {

                    $response = $this->showResponseAuthen($user);

                } else {

                    $response = $this->responseRequestError("ไม่สามารถเข้าสู้ระบบได้");

                }
            } else {

                $user = new User();
                $user->username = $this->strRandom() . '-' . date('YmdHis');
                $user->fullname = $request->fullname;
                $user->email = $request->email;
                $user->facebook_id = $request->facebook_id;
                $user->password = Hash::make($this->strRandom());
                $user->profile_image = $request->profile_image;

                if ($user->save()) {

                    $user_user_type = new UserUserType();
                    $user_user_type->user_id = $user->id;
                    $user_user_type->user_type_id = $request->user_type_id;

                    if ($user_user_type->save()) {

                        $response = $this->showResponseAuthen($user);

                    } else {

                        $response = $this->responseRequestError("ไม่สามารถเพิ่มประเภทผู้ใช้งานได้");

                    }

                } else {

                    $response = $this->responseRequestError("ไม่สามารถสมัครสมาชิกได้");

                }
            }
        }

        return $response;
    }

    /*
    |--------------------------------------------------------------------------
    | function สำหรับแสดงค่า response เวลา login และ register
    |--------------------------------------------------------------------------
     */
    protected function showResponseAuthen($user)
    {
        $token = $this->jwt($user);
        $user['user_user_type'] = UserType::join('user_user_type', 'user_type.id', 'user_user_type.user_type_id')
            ->where('user_user_type.user_id', $user->id)
            ->where('user_user_type.status_id', Status::$ACTIVE)
            ->get();
        $user['api_token'] = $token;

        return $this->responseRequestSuccess($user);
    }
}
