<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        \DB::table('social_provider_user')->delete();
        \DB::table('user_addresses')->delete();
        \DB::table('aspirasis')->delete();
        \DB::table('orders')->delete();
        \DB::table('carts')->delete();
        \DB::table('events')->delete();
        \DB::table('posts')->delete();
        \DB::table('pages')->delete();
        \DB::table('model_has_roles')->delete();
        \DB::table('users')->delete();

        \DB::table('users')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Wave Admin',
                'email' => 'admin@admin.com',
                'username' => 'admin',
                'avatar' => 'demo/default.png',
                'password' => '$2y$10$L8MjmjVVOCbyLHbp7pq/9.1ZEEa5AqE67ZXLd2M4.res05a3Rz/G2',
                'remember_token' => '4oXDVo48Lm1pc4j7NkWI9cMO4hv5OIEJFMrqjSCKQsIwWMGRFYDvNpdioBfo',
                'created_at' => '2017-11-21 16:07:22',
                'updated_at' => '2018-09-22 23:34:02',
                'trial_ends_at' => NULL,
                'verification_code' => NULL,
                'verified' => 1,
            ),
        ));
        
        
    }
}
