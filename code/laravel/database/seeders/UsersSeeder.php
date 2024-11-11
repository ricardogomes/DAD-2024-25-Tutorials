<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    private $photoPath;

    private $typesOfUsers =  ['A', 'P'];

    private $numberOfUsers = [6, 500];
    private $numberOfSoftDeletedUsers = [0, 45];
    private $numberOfBlocked = [0, 30];
    private $files_M = [];
    private $files_F = [];
    private $genres = [];

    public static $allUsers = [];

    private static $used_emails = [];
    private static $used_nicknames = [];
    public static $hashPasword = "";


    public function run()
    {
        $this->photoPath = storage_path('app/public/photos');
        $this->command->table(['Users table seeder notice'], [
            ['Photos will be stored on path ' . $this->photoPath]
        ]);

        $this->cleanFilesPhotos();
        $this->preencherFileNamesPhotos();

        $faker = \Faker\Factory::create(DatabaseSeeder::$seedLanguage);

        $variosUsers = [];
        $totalGuardados = 0;
        $totalParaGuardar = 0;
        foreach ($this->typesOfUsers as $idxType => $typeUser) {
            $totalParaGuardar += $this->numberOfUsers[$idxType];
        }
        $tmpDateTime = new Carbon();
        foreach ($this->typesOfUsers as $idxType => $typeUser) {
            $totalUsers = $this->numberOfUsers[$idxType];
            for ($i = 0; $i < $totalUsers; $i++) {
                $newUser = $this->newFakerUser($faker, $typeUser, $tmpDateTime);
                $variosUsers[] = $newUser;
                if (count($variosUsers) >= 50) {
                    $totalGuardados += count($variosUsers);
                    $this->command->info("Saved $totalGuardados/$totalParaGuardar users on the database");
                    DB::table('users')->insert($variosUsers);
                    $variosUsers = [];
                }
            }
        }
        if (count($variosUsers) > 0) {
            $totalGuardados += count($variosUsers);
            $this->command->info("Saved $totalGuardados/$totalParaGuardar users on the database");
            DB::table('users')->insert($variosUsers);
        }
        $this->orderAllTimestamps($faker);
        // UsersSeeder::$allUsers['A'] = DB::table('users')->where('type', 'A')->orderBy('id')->pluck('email', 'id');
        // UsersSeeder::$allUsers['P'] = DB::table('users')->where('type', 'P')->orderBy('id')->pluck('email', 'id');
        UsersSeeder::$allUsers['A'] = DB::table('users')->where('type', 'A')->orderBy('id')->pluck('email', 'id');
        UsersSeeder::$allUsers['P'] = DB::table('users')->where('type', 'P')->orderBy('id')->pluck('email', 'id');

        $this->fillGenders(UsersSeeder::$allUsers['A']);
        $this->fillGenders(UsersSeeder::$allUsers['P']);

        shuffle($this->files_M);
        shuffle($this->files_F);

        $this->copiarPhotos(UsersSeeder::$allUsers['A']);
        $this->copiarPhotos(UsersSeeder::$allUsers['P']->take(10));

        UsersSeeder::$allUsers['A'] = UsersSeeder::$allUsers['A']->shuffle();
        UsersSeeder::$allUsers['P'] = UsersSeeder::$allUsers['P']->shuffle();

        $idsToBlock = [];
        $idsToDelete = [];
        foreach ($this->typesOfUsers as $idxType => $typeUser) {
            $usersToBlock = $this->numberOfBlocked[$idxType];
            $usersToDelete = $this->numberOfSoftDeletedUsers[$idxType];
            foreach (UsersSeeder::$allUsers[$typeUser] as $user) {
                if ($usersToBlock > 0) {
                    $idsToBlock[] = $user['id'];
                    $usersToBlock--;
                } elseif (($usersToBlock == 0) && ($usersToDelete > 0)) {
                    $idsToDelete[] = $user['id'];
                    $usersToDelete--;
                }
                if (($usersToBlock == 0) && ($usersToDelete == 0)) {
                    continue;
                }
            }
        }
        if (count($idsToBlock) > 0) {
            $this->command->info("Block " . count($idsToBlock) . " users on the database");
            DB::table('users')->whereIn('id', $idsToBlock)->update(['blocked' => 1]);
        }
        if (count($idsToDelete) > 0) {
            $this->command->info("Soft Delete " . count($idsToDelete) . " users on the database");
            DB::table('users')->whereNotIn('id', $idsToDelete)->update(['deleted_at' => null]);
        }


        $this->command->info("Update first Administrators and Players with known email");

        // Administrators
        $idsAdmins = DB::table('users')
            ->where('type', 'A')
            ->where('blocked', 0)
            ->whereNull('deleted_at')
            ->whereNotNull('photo_filename')
            ->orderBy('id')
            ->take(2)
            ->pluck('id');
        foreach ($idsAdmins as $key => $id) {
            $idx = $key + 1;
            DB::table('users')->where('id', $id)->update(['email' => "a$idx@mail.pt"]);
        }

        // Players - older
        $idsPlayers = DB::table('users')
            ->where('type', 'P')
            ->where('blocked', 0)
            ->whereNull('deleted_at')
            ->whereNotNull('photo_filename')
            ->orderBy('id')
            ->take(5)
            ->pluck('id');
        foreach ($idsPlayers as $key => $id) {
            $idx = $key + 1;
            DB::table('users')->where('id', $id)->update(['email' => "p$idx@mail.pt"]);
        }

        // Players - newer
        $idsPlayers = DB::table('users')
            ->where('type', 'P')
            ->where('blocked', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->take(5)
            ->pluck('id');
        $idx = 10;
        foreach ($idsPlayers as $key => $id) {
            DB::table('users')->where('id', $id)->update(['email' => "p$idx@mail.pt"]);
            $idx--;
        }

        $playersWithNoPhoto = DB::table('users')
            ->where('type', 'P')
            ->whereNotIn('email', [
                "p6@mail.pt",
                "p7@mail.pt",
                "p8@mail.pt",
                "p9@mail.pt",
                "p10@mail.pt",
            ])
            ->whereNull('photo_filename')->pluck('email', 'id');
        $this->fillGenders($playersWithNoPhoto);
        $playersWithNoPhoto = $playersWithNoPhoto->shuffle();
        $this->copiarPhotos($playersWithNoPhoto);
    }

    private function fillGenders($users_array)
    {
        foreach ($users_array as $key => $value) {
            $users_array[$key] = [
                "id" => $key,
                "email" => $value,
                "genre" => $this->genres[$value]
            ];
        }
    }

    private function cleanFilesPhotos()
    {
        File::deleteDirectory($this->photoPath);
        File::makeDirectory($this->photoPath);
    }

    private function preencherFileNamesPhotos()
    {
        $allFiles = collect(File::files(database_path('seeders/photos')));
        foreach ($allFiles as $f) {
            if (strpos($f->getPathname(), 'm_')) {
                $this->files_M[] = $f->getPathname();
            } else {
                $this->files_F[] = $f->getPathname();
            }
        }
    }

    private function copiarPhotos($arrayUsers)
    {
        foreach ($arrayUsers as $user) {
            if ((count($this->files_M) == 0) && (count($this->files_F) == 0)) {
                break;
            }
            $file = $user['genre'] == 'M' ? array_shift($this->files_M) : array_shift($this->files_F);
            if ($file) {
                $this->savePhotoOfUser($user['id'], $file);
            }
        }
    }

    private function savePhotoOfUser($id, $file)
    {
        $newfilename = $id . "_" . uniqid() . '.jpg';
        File::copy($file, $this->photoPath . '/' . $newfilename);
        DB::table('users')->where('id', $id)->update(['photo_filename' => $newfilename]);
        $this->command->info("Atualizada photo do user $id. Name do file copiado = $newfilename");
    }

    private static function stripAccents($stripAccents)
    {
        $from = 'àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ';
        $to =   'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY';
        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        return strtr($stripAccents, $mapping);
    }

    private function strtr_utf8($str, $from, $to)
    {
        $keys = array();
        $values = array();
        preg_match_all('/./u', $from, $keys);
        preg_match_all('/./u', $to, $values);
        $mapping = array_combine($keys[0], $values[0]);
        return strtr($str, $mapping);
    }

    public function randomName($faker, &$gender, &$fullname, &$email,  &$nickname, $allowRepeated = false)
    {
        $gender = $faker->randomElement(['male', 'female']);
        $firstname = $faker->firstName($gender);
        $lastname = $faker->lastName();
        $secondname = $faker->numberBetween(1, 3) == 2 ? "" : " " . $faker->firstName($gender);
        $number_middlenames = $faker->numberBetween(1, 6);
        $number_middlenames = $number_middlenames == 1 ? 0 : ($number_middlenames >= 5 ? $number_middlenames - 3 : 1);
        $middlenames = "";
        for ($i = 0; $i < $number_middlenames; $i++) {
            $middlenames .= " " . $faker->lastName();
        }
        $fullname = $firstname . $secondname . $middlenames . " " . $lastname;
        $nickname = strtolower(rand(1, 4) == 3 ? self::stripAccents($lastname) : self::stripAccents($firstname));
        $email = strtolower(self::stripAccents($firstname) . "." . self::stripAccents($lastname) . "@mail.pt");
        if (!$allowRepeated) {
            $i = 2;
            while (in_array($email, self::$used_emails)) {
                $email = strtolower(self::stripAccents($firstname) . "." . self::stripAccents($lastname) . "." . $i . "@mail.pt");
                $i++;
            }

            $i = 2;
            while (in_array($nickname, self::$used_nicknames)) {
                $nickname = strtolower(rand(1, 4) == 3 ? self::stripAccents($lastname) : self::stripAccents($firstname));
                $nickname .= $i;
                $i++;
            }
        }
        self::$used_emails[] = $email;
        self::$used_nicknames[] = $nickname;
        $gender = $gender == 'male' ? 'M' : 'F';
    }

    private function newFakerUser($faker, $type, $tmpDateTime)
    {
        $fullname = "";
        $email = "";
        $gender = "";
        $nickname = "";
        $this->randomName($faker, $gender, $fullname, $email, $nickname);
        $this->genres[$email] = $gender;
        if (self::$hashPasword == "") {
            self::$hashPasword = bcrypt('123');
        }
        return [
            'name' => $fullname,
            'nickname' => $nickname,
            'email' => $email,
            'email_verified_at' => $tmpDateTime,
            'password' => self::$hashPasword,
            'remember_token' => Str::random(10),
            'created_at' => $tmpDateTime,
            'updated_at' => $tmpDateTime,
            'type' => $type,
            'blocked' => 0,
            'brain_coins_balance' => 0,
            'deleted_at' => $tmpDateTime,
        ];
    }

    private function orderAllTimestamps($faker)
    {
        $this->command->info("Updating all timestamps of users");
        $allUsers = DB::table('users')->orderBy('type', 'asc')->orderBy('id', 'asc')->get();
        $totalUsers = $allUsers->count();
        $randomDates = [];
        for ($i = 0; $i < $totalUsers; $i++) {
            $randomDates[] = Carbon::today()->subDays(rand(1, 365))->addSeconds(rand(0, 60*60*24));
        }
        $randomDatesCollection = collect($randomDates);
        $orderedDates = $randomDatesCollection->sort(function ($a, $b) {
            if ($a->eq($b)) {
                return 0;
            }
            return $a->lt($b) ? -1 : 1;
        })->values();
        $currentTime = new Carbon();
        $i = 0;
        foreach ($allUsers as $user) {
            $createdAt = $orderedDates[$i];
            $email_verified_at = $createdAt->copy()->addSeconds(rand(1, 3600));
            if ($email_verified_at->gt($currentTime)) {
                $email_verified_at = $currentTime->copy();
            }
            $diffInSeconds = $email_verified_at->diffInSeconds($currentTime);
            $randomBetweenSeconds = rand(0, $diffInSeconds - 3);

            $updatedAt = $currentTime->copy()->subSeconds($randomBetweenSeconds);
            if ($updatedAt->gt($currentTime)) {
                $updatedAt = $currentTime->copy();
            }
            $deletedAt = $updatedAt->copy();
            DB::table('users')->where('id', $user->id)->update([
                'created_at' => $createdAt,
                'email_verified_at' => $email_verified_at,
                'updated_at' => $updatedAt,
                'deleted_at' => $deletedAt
            ]);
            $i++;
            if ($i % 100 == 0) {
                $this->command->info("Updated 100 timestamps of users");
            }
        }
    }

    /*
    public static function randomPaymentInfo($faker, $email, &$paymentType, &$paymentReference)
    {
        $paymentType = $faker->randomElement(['VISA', 'MBWAY', 'PAYPAL']);
        switch ($paymentType) {
            case 'VISA':
                $paymentReference = rand(4, 6) . $faker->randomNumber($nbDigits = 8, $strict = true) . $faker->randomNumber($nbDigits = 7, $strict = true);
                break;
            case 'MBWAY':
                $paymentReference = '9' . $faker->randomNumber($nbDigits = 8, $strict = true);
                break;
            case 'PAYPAL':
                $paymentReference = $email;
                break;
        }
    }
    */
}


