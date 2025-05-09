<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>A simple Calculator</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
            color: #333;
        }
        
        .calculator-container {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 30px;
            width: 350px;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        
        h2 {
            color: #5e72e4;
            margin-top: 0;
            margin-bottom: 30px;
            font-weight: 600;
            font-size: 28px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        input[type="number"], select {
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        input[type="number"]:focus, select:focus {
            border-color: #5e72e4;
            box-shadow: 0 0 0 2px rgba(94, 114, 228, 0.2);
        }
        
        select {
            cursor: pointer;
            background-color: white;
        }
        
        input[type="submit"] {
            background: linear-gradient(to right, #5e72e4, #825ee4);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(94, 114, 228, 0.4);
        }
        
        .result {
            margin-top: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            color: #5e72e4;
            display: inline-block;
        }
        
        .operation-container {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .operation-container input, .operation-container select {
            flex: 1;
        }
    </style>
</head>
<body>
<div class="calculator-container">
    <h2>My Simple Calculator</h2>
    <form method="GET">
        <input type="number" name="num1" placeholder="Enter first number" required>
        <div class="operation-container">
            <select name="operation">
                <option value="addition">+</option>
                <option value="subtraction">-</option>
                <option value="multiplication">×</option>
                <option value="division">÷</option>
            </select>
        </div>
        <input type="number" name="num2" placeholder="Enter second number" required>
        <input type="submit" name="submit" value="Calculate">
    </form>

    <?php
    if(isset($_GET['submit'])) {
        $num1 = $_GET['num1'];
        $num2 = $_GET['num2'];
        $operation = $_GET['operation'];

        // set the value of result to zero
        $result = 0;

        switch ($operation) {
            case 'addition':
                $result = $num1 + $num2;
                break;   
            case 'subtraction':
                $result = $num1 - $num2;
                break;
            case 'multiplication':
                $result = $num1 * $num2;
                break;
            case "division":
                $result = ($num2 != 0) ? $num1 / $num2 : "Error: Cannot divide by zero!";
                break;
            default:
                $result = "Invalid operation.";
        }

        echo "<div class='result'>Result: $result</div>";
    }
    ?>
</div>
</body>
</html>