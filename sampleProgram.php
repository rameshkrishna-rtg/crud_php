<?php

require '../Config/db_Connection.php';
require_once '../Config/cors.php';
date_default_timezone_set('Asia/Kolkata');

session_start();
$c_mobileno = $_COOKIE['mobileno'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    try {

        $input = json_decode(file_get_contents("php://input"), true);

        if (! $input) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
            exit;
        }

        $sub_username = $input['name'] ?? null;
        $mobileno     = $input['mobilenumber'] ?? null;
        $sitedetails  = $input['sitedetails'] ?? [];
        $role         = $input['role'] ?? null;
        $status       = $input['status'] ?? null;

        if (! $sub_username || ! $mobileno || ! $role || ! $status) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Missing required fields"]);
            exit;
        }

       
        $select_stmt = $conn->prepare("SELECT COUNT(*) FROM subuser WHERE mobilenumber = :mobileno");
        $select_stmt->execute([':mobileno' => $mobileno]);
        if ($select_stmt->fetchColumn() > 0) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => "Mobile number already exists"]);
            exit;
        }

        
        $conn->beginTransaction();

       
        $stmt = $conn->prepare("
            INSERT INTO subuser (name, mobilenumber, role, status)
            VALUES (:name, :mobileno, :role, :status)
        ");

        if (! $stmt->execute([
            ':name'     => $sub_username,
            ':mobileno' => $mobileno,
            ':role'     => $role,
            ':status'   => $status,
        ])) {
            throw new Exception("Subuser insert failed");
        }

        $subuser_id = $conn->lastInsertId();

        if ($role === 'SUPER_ADMIN') {

           
            $rawUsername = $sitedetails[0]['username'] ?? null;
            $rawSites    = $sitedetails[0]['sites'] ?? null;

            
            if (is_array($rawSites)) {
                $rawSites = implode(',', $rawSites);
            }

            $upd = $conn->prepare("
                UPDATE subuser
                SET username = :username,
                    SiteName = :sites
                WHERE id = :id
            ");

            $upd->execute([
                ':username' => $rawUsername,
                ':sites'    => $rawSites,
                ':id'       => $subuser_id,
            ]);

            $conn->commit();

            http_response_code(201);
            echo json_encode([
                "status"  => "success",
                "message" => "SUPER_ADMIN stored as provided",
            ]);
            exit;
        }

        if ($role === 'USER') {

            if (empty($sitedetails) || empty($sitedetails[0]['username'])) {
                throw new Exception("Username (corporation) is required for USER");
            }

            $corpUsername = trim($sitedetails[0]['username']);

            $siteNames = [];

            foreach ($sitedetails as $block) {

                $sites = $block['sites'] ?? [];

                if (is_string($sites) && strtolower($sites) === 'all') {
                    throw new Exception("USER role cannot have ALL access");
                }

                if (is_array($sites)) {
                    foreach ($sites as $s) {
                        $siteNames[] = $s;
                    }
                }
            }

            if (empty($siteNames)) {
                throw new Exception("At least one site is required for USER");
            }

            $finalSites = implode(',', $siteNames);

        
            $upd = $conn->prepare("
                UPDATE subuser
                SET username = :username,
                    SiteName = :sites
                WHERE id = :id
            ");

            $upd->execute([
                ':username' => $corpUsername,
                ':sites'    => $finalSites,
                ':id'       => $subuser_id,
            ]);

            $conn->commit();

            http_response_code(201);
            echo json_encode([
                "status"  => "success",
                "message" => "USER created successfully",
            ]);
            exit;
        }

    
        $usernames = [];

        foreach ($sitedetails as $block) {
            if (! empty($block['username'])) {
                $usernames[] = trim($block['username']);
            }
        }

        if (empty($usernames)) {
            throw new Exception("At least one username is required");
        }

        $finalUsernames = implode(',', array_unique($usernames));

       
        $updUser = $conn->prepare("
            UPDATE subuser
            SET username = :username
            WHERE id = :id
        ");

        $updUser->execute([
            ':username' => $finalUsernames,
            ':id'       => $subuser_id,
        ]);

      

        foreach ($sitedetails as $block) {

            $uname = $block['username'];
            $sites = $block['sites'];

            if (is_string($sites)) {
                $sites = [$sites];
            }

           
            $user_stmt = $conn->prepare("SELECT id FROM userdetail WHERE username = :username");
            $user_stmt->execute([':username' => $uname]);
            $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);

            if (! $user_row) {
                throw new Exception("User not found: $uname");
            }

            $user_id = $user_row['id'];

          
            if (count($sites) === 1 && strtolower($sites[0]) === 'all') {

                $insert_access = $conn->prepare("
                    INSERT INTO subuser_access (subuser_id, user_id, site_id, access_type)
                    VALUES (:subuser_id, :user_id, NULL, 'ALL')
                ");

                $insert_access->execute([
                    ':subuser_id' => $subuser_id,
                    ':user_id'    => $user_id,
                ]);

                continue;
            }

           
            foreach ($sites as $sname) {

                if (strtolower($sname) === 'all') {
                    continue;
                }

                $site_stmt = $conn->prepare("
                    SELECT id FROM sites
                    WHERE username = :username AND siteName = :siteName
                ");

                $site_stmt->execute([
                    ':username' => $uname,
                    ':siteName' => $sname,
                ]);

                $site_row = $site_stmt->fetch(PDO::FETCH_ASSOC);

                if (! $site_row) {
                    throw new Exception("Site not found: $sname");
                }

                $site_id = $site_row['id'];

                $insert_access = $conn->prepare("
                    INSERT INTO subuser_access (subuser_id, user_id, site_id, access_type)
                    VALUES (:subuser_id, :user_id, :site_id, 'LIMITED')
                ");

                $insert_access->execute([
                    ':subuser_id' => $subuser_id,
                    ':user_id'    => $user_id,
                    ':site_id'    => $site_id,
                ]);
            }
        }

       
        $conn->commit();

        http_response_code(201);
        echo json_encode([
            "status"  => "success",
            "message" => "Subuser created successfully",
        ]);

    } catch (Exception $e) {

        if ($conn->inTransaction()) {
            $conn->rollBack();
        }

        http_response_code(500);
        echo json_encode([
            "status"  => "error",
            "message" => $e->getMessage(),
        ]);
    }
}