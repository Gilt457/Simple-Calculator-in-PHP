<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/reset.css">
    <link rel="stylesheet" href="CSS/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Professional Calculator</title>
</head>
<body>
    <div class="calculator-container">
        <div class="calculator-header">
            <h1><i class="fas fa-calculator"></i> Professional Calculator</h1>
        </div>
        <div class="calculator-body">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <div class="input-group">
                    <label for="num1">First Number</label>
                    <input type="number" id="num1" name="num1" placeholder="Enter first number" step="any">
                </div>
                
                <div class="operation-selector">
                    <label for="operation">Operation</label>
                    <select id="operation" name="operation">
                        <option value="addition">Addition (+)</option>
                        <option value="substraction">Subtraction (-)</option>
                        <option value="multiplication">Multiplication (×)</option>
                        <option value="division">Division (÷)</option>
                    </select>
                </div>
                
                <div class="input-group">
                    <label for="num2">Second Number</label>
                    <input type="number" id="num2" name="num2" placeholder="Enter second number" step="any">
                </div>
                
                <button type="submit"><i class="fas fa-equals"></i> Calculate</button>            </form>
            
            <div class="result-container">
            <?php
    if($_SERVER['REQUEST_METHOD'] == "POST") {
        // Grab data from input
        $num1 = filter_input(INPUT_POST, "num1", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $num2 = filter_input(INPUT_POST, "num2", FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $operator = htmlspecialchars($_POST['operation']);

        // Error Handler
        $errors = false;

        if(empty($num1) || empty($num2) || empty($operator)) {
            echo "<div class='calc-error'><i class='fas fa-exclamation-triangle'></i> Please fill in all fields</div>";
            $errors = true;
        }

        if(!is_numeric($num1) || !is_numeric($num2)) {
            echo "<div class='calc-error'><i class='fas fa-exclamation-triangle'></i> Please enter valid numbers</div>";
            $errors = true;
        }

        // Calculate the numbers if there is no error
        if(!$errors) {
            $value = 0;
            $operation_symbol = "";
            
            switch($operator) {
                case "addition":
                    $value = $num1 + $num2;
                    $operation_symbol = "+";
                    break;
                case "substraction":
                    $value = $num1 - $num2;
                    $operation_symbol = "-";
                    break;
                case "multiplication":
                    $value = $num1 * $num2;
                    $operation_symbol = "×";
                    break;
                case "division":
                    if($num2 != 0) {
                        $value = $num1 / $num2;
                        $operation_symbol = "÷";
                    } else {
                        echo "<div class='calc-error'><i class='fas fa-exclamation-triangle'></i> Cannot divide by zero</div>";
                        $errors = true;
                    }
                    break;
                default:
                    echo "<div class='calc-error'><i class='fas fa-exclamation-triangle'></i> Something went wrong!</div>";
                    $errors = true;
            }

            if(!$errors) {
                echo "<div class='calc-result'>";
                echo "<div class='operation-display'>$num1 $operation_symbol $num2</div>";
                echo "<div class='result-value'><i class='fas fa-equals'></i> " . number_format($value, is_int($value) ? 0 : 2) . "</div>";
                echo "</div>";
            }
        }
    }
    ?>
            </div>
        </div>
        <div class="calculator-footer">
            <p>&copy; <?php echo date("Y"); ?> Professional Calculator</p>
        </div>
    </div>
</body>
</html>
