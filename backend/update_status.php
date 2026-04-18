<?php
ob_start();
session_start();
require 'db_conn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$email_sent = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'];

    $employee_name = isset($_SESSION['first_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Staff';

    if ($action === 'receive_order') {
        $order_id = $_POST['order_id'];
        $upd = $conn->prepare("UPDATE `Process_Load` SET status = 'In Queue' WHERE order_id = ? AND status = 'Pending Dropoff'");
        $upd->bind_param("s", $order_id);
        $upd->execute();

        $conn->query("UPDATE `Order` SET status = 'In Progress' WHERE order_id = '$order_id'");
        $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name marked the order as received. All pending bags moved to Queue.')");

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    if ($action === 'complete_order') {
        $order_id = $_POST['order_id'];
        $upd = $conn->prepare("UPDATE `Process_Load` SET status = 'Completed' WHERE order_id = ? AND status = 'Ready for Pickup'");
        $upd->bind_param("s", $order_id);
        $upd->execute();

        $conn->query("UPDATE `Order` SET status = 'Completed' WHERE order_id = '$order_id'");
        $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name completed the order. Customer picked up the laundry.')");

        // --- Email Thank You / Pickup Confirmation ---
        $orderQuery = $conn->query("SELECT o.customer_name, o.services_requested, o.final_price, u.email FROM `Order` o JOIN `User` u ON o.customer_id = u.user_id WHERE o.order_id = '$order_id'");

        if ($orderQuery && $orderQuery->num_rows > 0) {
            $orderData = $orderQuery->fetch_assoc();
            $customerEmail = $orderData['email'];

            if (!empty($customerEmail)) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sevillaralph1504@gmail.com';
                    $mail->Password   = 'wagc ultm nqrk hnfp';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
                    $mail->addAddress($customerEmail);
                    $mail->isHTML(true);
                    $mail->Subject = "Thank You! Laundry Picked Up Successfully (Order #$order_id)";

                    $price = number_format($orderData['final_price'], 2);
                    $mail->Body = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px; margin: 0 auto;'>
                            <h2 style='color: #0d6efd; text-align: center;'>Thank You for Choosing LABAssistance!</h2>
                            <p>Hello <strong>" . htmlspecialchars($orderData['customer_name']) . "</strong>,</p>
                            <p>This is a confirmation that your laundry order has been successfully picked up from our shop.</p>
                            <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                <h3 style='margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 5px; color: #333;'>Final Order Details</h3>
                                <p><strong>Order ID:</strong> #{$order_id}</p>
                                <p><strong>Services Completed:</strong> {$orderData['services_requested']}</p>
                                <p><strong>Total Paid:</strong> ₱{$price}</p>
                            </div>
                            <p>We hope you are fully satisfied with our services. Have a great day and we look forward to serving you again soon!</p>
                        </div>
                    ";
                    $mail->send();
                    $email_sent = true;
                    $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', 'Pickup confirmation & Thank You email sent to customer.')");
                } catch (Exception $e) {
                }
            }
        }

        if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'email_sent' => $email_sent]);
            exit();
        }

        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    $load_id = $_POST['load_id'] ?? 0;
    if ($load_id) {
        $stmt = $conn->prepare("SELECT pl.*, o.services_requested, o.final_price, o.customer_name 
                                FROM `Process_Load` pl 
                                JOIN `Order` o ON pl.order_id = o.order_id 
                                WHERE pl.load_id = ?");
        $stmt->bind_param("i", $load_id);
        $stmt->execute();
        $bag = $stmt->get_result()->fetch_assoc();

        $services = $bag['services_requested'];
        $curr = $bag['status'];
        $order_id = $bag['order_id'];
        $bag_label = $bag['bag_label'];

        if ($action === 'update_phase') {

            $target = $_POST['target_phase'] ?? 'Ready for Pickup';

            $upd = $conn->prepare("UPDATE `Process_Load` SET status = ? WHERE load_id = ?");
            $upd->bind_param("si", $target, $load_id);
            $upd->execute();

            $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', '$employee_name moved $bag_label from $curr to $target.')");

            // --- Email Completion Report (Only runs if target phase is Ready for Pickup) ---
            if ($target === 'Ready for Pickup') {
                $not_ready_query = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND status IN ('Pending Dropoff', 'In Queue', 'Washing', 'Drying', 'Folding')");

                if ($not_ready_query && $not_ready_query->fetch_assoc()['c'] == 0) {
                    $orderQuery = $conn->query("SELECT email FROM `User` WHERE user_id = (SELECT customer_id FROM `Order` WHERE order_id = '$order_id')");
                    if ($orderQuery && $orderQuery->num_rows > 0) {
                        $customerEmail = $orderQuery->fetch_assoc()['email'];
                        if (!empty($customerEmail)) {
                            $shopQuery = $conn->query("SELECT IFNULL(current_closing_time, default_close_time) as close_time FROM `Shop_Status` WHERE status_id = 1");
                            $close_time_formatted = date("g:i A", strtotime($shopQuery->fetch_assoc()['close_time'] ?? '20:00:00'));

                            $mail = new PHPMailer(true);
                            try {
                                $mail->isSMTP();
                                $mail->Host       = 'smtp.gmail.com';
                                $mail->SMTPAuth   = true;
                                $mail->Username   = 'sevillaralph1504@gmail.com';
                                $mail->Password   = 'wagc ultm nqrk hnfp';
                                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                                $mail->Port       = 587;

                                $mail->setFrom('sevillaralph1504@gmail.com', 'LABAssistance Support');
                                $mail->addAddress($customerEmail);
                                $mail->isHTML(true);
                                $mail->Subject = "Your Laundry is Ready for Pick-up! (Order #$order_id)";

                                $price = number_format($bag['final_price'], 2);
                                $mail->Body = "
                                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #ddd; max-width: 600px; margin: 0 auto;'>
                                        <h2 style='color: #198754; text-align: center;'>Laundry Ready for Pick-up!</h2>
                                        <p>Hello <strong>" . htmlspecialchars($bag['customer_name']) . "</strong>,</p>
                                        <p>Great news! Your laundry order is completely finished and ready for you to pick up.</p>
                                        <div style='background-color: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                                            <h3 style='margin-top: 0; border-bottom: 2px solid #ddd; padding-bottom: 5px; color: #333;'>Completion Report</h3>
                                            <p><strong>Order ID:</strong> #{$order_id}</p>
                                            <p><strong>Services Completed:</strong> {$services}</p>
                                            <p><strong>Total Amount Due:</strong> ₱{$price}</p>
                                        </div>
                                        <p>Please visit our shop at your earliest convenience to collect your items. <strong style='color: #dc3545;'>Please note that our shop closes at {$close_time_formatted} today.</strong></p><br>
                                        <p>Thank you for choosing LABAssistance!</p>
                                    </div>
                                ";
                                $mail->send();
                                $email_sent = true;
                                $conn->query("INSERT INTO `Order_Logs` (order_id, log_message) VALUES ('$order_id', 'Completion report successfully emailed to customer.')");
                            } catch (Exception $e) {
                            }
                        }
                    }
                }
            }
        }
    }

    if (isset($_POST['is_ajax']) && $_POST['is_ajax'] == '1') {
        ob_end_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'email_sent' => $email_sent]);
        exit();
    }

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}
