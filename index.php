<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/reset.css">
    <link rel="stylesheet" href="CSS/main.css"> <!-- Assuming this will be updated or a new CSS file will be used -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>Scientific Calculator</title>
    <style>
        /* Basic styling for error messages, can be moved to main.css */
        .calc-error { color: red; font-weight: bold; margin-top: 10px; }
        .calc-result { margin-top: 10px; }
        .operation-display { font-style: italic; color: #555; }
        .result-value { font-size: 1.2em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="calculator-container">
        <div class="calculator-header">
            <h1><i class="fas fa-calculator"></i> Scientific Calculator</h1>
        </div>
        <div class="calculator-body calculator-grid-container">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <?php
                // This top PHP block is for initializing variables that will be used
                // both in the form fields and in the result display area.
                // session_start(); // Will be moved to the very top of the file.

                $php_display_output = '0'; // Default for the main display input field
                $result_value = '';        // For the result line
                $error_message = '';       // For error messages
                $input_expression_for_result_area = ''; // To show "Processed: [expression]"

                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                    if (isset($_POST['expression'])) {
                        $posted_expression = trim($_POST['expression']);
                        $input_expression_for_result_area = htmlspecialchars($posted_expression); // Show what was posted
                        $php_display_output = htmlspecialchars($posted_expression); // Initially, display shows the posted expression

                        $is_memory_operation = false;

                        // Handle Memory Operations
                        if ($posted_expression === 'memory_clear') {
                            unset($_SESSION['calculator_memory']);
                            $result_value = "Memory Cleared";
                            $input_expression_for_result_area = "MC";
                            $php_display_output = '0'; // Clear display after MC
                            $is_memory_operation = true;
                        } elseif ($posted_expression === 'memory_recall') {
                            if (isset($_SESSION['calculator_memory']) && is_numeric($_SESSION['calculator_memory'])) {
                                $php_display_output = htmlspecialchars((string)$_SESSION['calculator_memory']); // Update main display
                                $result_value = ""; // No specific result line, value is in display
                                $input_expression_for_result_area = "MR -> " . $php_display_output;
                            } else {
                                $error_message = "Memory is empty or invalid.";
                                $php_display_output = '0'; // Or 'Error'
                            }
                            $is_memory_operation = true;
                        } elseif (strpos($posted_expression, 'memory_add:') === 0) {
                            $parts = explode(':', $posted_expression, 2);
                            // Value to add should be the result of the *previous* calculation,
                            // or the current display if nothing was calculated.
                            // The JS sends the current display's content.
                            $value_to_process = $parts[1] ?? null;

                            // Attempt to evaluate the value_to_process if it's an expression itself
                            // For simplicity, we assume value_to_process is a number or simple expression from display
                            // A robust solution might evaluate value_to_process here using the calculator's engine
                            $numeric_value_to_add = filter_var($value_to_process, FILTER_VALIDATE_FLOAT);

                            if ($numeric_value_to_add !== false) {
                                if (!isset($_SESSION['calculator_memory'])) {
                                    $_SESSION['calculator_memory'] = 0;
                                }
                                $_SESSION['calculator_memory'] += $numeric_value_to_add;
                                $result_value = "Added to Memory. New Mem: " . $_SESSION['calculator_memory'];
                                $php_display_output = htmlspecialchars($value_to_process); // Keep display as it was
                            } else {
                                $error_message = "Invalid value for M+: '" . htmlspecialchars($value_to_process) . "'";
                                $php_display_output = htmlspecialchars($value_to_process); // Show the problematic value
                            }
                            $input_expression_for_result_area = "M+ (" . htmlspecialchars($value_to_process) . ")";
                            $is_memory_operation = true;
                        } elseif (strpos($posted_expression, 'memory_subtract:') === 0) {
                            $parts = explode(':', $posted_expression, 2);
                            $value_to_process = $parts[1] ?? null;
                            $numeric_value_to_subtract = filter_var($value_to_process, FILTER_VALIDATE_FLOAT);

                            if ($numeric_value_to_subtract !== false) {
                                if (!isset($_SESSION['calculator_memory'])) {
                                    $_SESSION['calculator_memory'] = 0;
                                }
                                $_SESSION['calculator_memory'] -= $numeric_value_to_subtract;
                                $result_value = "Subtracted from Memory. New Mem: " . $_SESSION['calculator_memory'];
                                $php_display_output = htmlspecialchars($value_to_process);
                            } else {
                                $error_message = "Invalid value for M-: '" . htmlspecialchars($value_to_process) . "'";
                                $php_display_output = htmlspecialchars($value_to_process);
                            }
                            $input_expression_for_result_area = "M- (" . htmlspecialchars($value_to_process) . ")";
                            $is_memory_operation = true;
                        }

                        // Mathematical Expression Processing (if not a memory operation)
                        if (!$is_memory_operation) {
                            if (empty(trim($posted_expression))) {
                                 $error_message = "Error: Expression is empty.";
                                 $php_display_output = '0';
                            } else {
                                // Sanitization (basic, as before)
                                $sanitized_expression_check = preg_replace('/[^0-9a-z\s\.\+\-\*\/\^\%\(\)π√e]/i', '', $posted_expression);
                                if ($sanitized_expression_check !== $posted_expression) {
                                    $error_message = "Error: Invalid characters in expression.";
                                    // $php_display_output retains the problematic expression for user to see
                                } else {
                                    try {
                                        $tokens = tokenize($posted_expression);
                                        $rpnQueue = shuntingYard($tokens);
                                        $calculated_value = evaluateRPN($rpnQueue);

                                        $result_value = htmlspecialchars(number_format($calculated_value, 10, '.', ''));
                                        $php_display_output = $result_value; // Main display shows the result
                                        // $input_expression_for_result_area is already set to the original posted_expression

                                    } catch (Exception $e) {
                                        $error_message = $e->getMessage();
                                        // $php_display_output retains the problematic expression
                                    }
                                }
                            }
                        }
                    } elseif (isset($_POST['expression']) && empty(trim($_POST['expression'])) && $_SERVER['REQUEST_METHOD'] == "POST" ) {
                         $error_message = "Error: Expression is empty.";
                         $php_display_output = '0';
                    }
                } else { // Not a POST request
                    // If there's a value in session memory, perhaps show it or an indicator?
                    // For now, JS handles initial '0' display. PHP sets '0' if no POST.
                    // $php_display_output = '0'; // Already default
                }
                ?>
                <input type="text" name="display" id="display" readonly placeholder="0" value="<?php echo $php_display_output; ?>">
                <input type="hidden" name="expression" id="expression" value=""> <!-- JS will fill this before submit -->

                <button type="button" class="btn-memory">MC</button>
                <button type="button" class="btn-memory">MR</button>
                <button type="button" class="btn-memory">M+</button>
                <button type="button" class="btn-memory">M-</button>
                <button type="button" class="btn-delete">DEL</button>

                <button type="button" class="btn-function" value="sin">sin</button>
                <button type="button" class="btn-function" value="cos">cos</button>
                <button type="button" class="btn-function" value="tan">tan</button>
                <button type="button" class="btn-function" value="asin">asin</button>
                <button type="button" class="btn-clear">AC</button>

                <button type="button" class="btn-function" value="acos">acos</button>
                <button type="button" class="btn-function" value="atan">atan</button>
                <button type="button" class="btn-function" value="log">log</button>
                <button type="button" class="btn-function" value="ln">ln</button>
                <button type="button" class="btn-operator" value="/">/</button>

                <button type="button" class="btn-function" value="^">x<sup>y</sup></button>
                <button type="button" class="btn-number" value="7">7</button>
                <button type="button" class="btn-number" value="8">8</button>
                <button type="button" class="btn-number" value="9">9</button>
                <button type="button" class="btn-operator" value="*">*</button>

                <button type="button" class="btn-function" value="sqrt">√</button>
                <button type="button" class="btn-number" value="4">4</button>
                <button type="button" class="btn-number" value="5">5</button>
                <button type="button" class="btn-number" value="6">6</button>
                <button type="button" class="btn-operator" value="-">-</button>

                <button type="button" class="btn-function" value="(">(</button>
                <button type="button" class="btn-number" value="1">1</button>
                <button type="button" class="btn-number" value="2">2</button>
                <button type="button" class="btn-number" value="3">3</button>
                <button type="button" class="btn-operator" value="+">+</button>

                <button type="button" class="btn-function" value=")">)</button>
                <button type="button" class="btn-function" value="±">±</button>
                <button type="button" class="btn-number" value="0">0</button>
                <button type="button" class="btn-number" value=".">.</button>
                <button type="submit" class="btn-equals" value="=">=</button>

                <button type="button" class="btn-function" value="%">%</button>
                <button type="button" class="btn-function" value="1/x">1/x</button>
                <button type="button" class="btn-function" value="pi">π</button>
                <button type="button" class="btn-function" value="e">e</button>
            </form>

            <div class="result-container">
            <?php
                // --- Calculator Functions (isOperator, isFunction, etc.) ---
                // These are assumed to be defined below this block or included.
                // For this diff, we're focusing on the main logic block.
                // The actual function definitions from the previous step are still needed.

                // --- Displaying Results / Errors ---
                if (!empty($error_message)) {
                    echo "<div class='calc-error'><i class='fas fa-exclamation-triangle'></i> " . htmlspecialchars($error_message) . "</div>";
                } elseif (!empty($result_value) || !empty($input_expression_for_result_area) ) {
                    // Show result if $result_value is set (calculation or memory op message)
                    // Or if there was an input expression processed (e.g. memory recall that puts value in display)
                    echo "<div class='calc-result'>";
                    if (!empty($input_expression_for_result_area)) {
                        echo "<div class='operation-display'>Processed: " . $input_expression_for_result_area . "</div>";
                    }
                    if (is_numeric(str_replace(',', '', $result_value))) { // Check if it's a numeric result
                        echo "<div class='result-value'><i class='fas fa-equals'></i> " . $result_value . "</div>";
                    } elseif (!empty($result_value)) { // Non-numeric result_value (e.g. "Memory Cleared")
                         echo "<div class='result-value'>" . htmlspecialchars($result_value) . "</div>";
                    }
                    echo "</div>";
                }

                // --- Calculator Logic (Function definitions like isOperator, tokenize, etc.) ---
                // This is where the function definitions from the previous step should be.
                // Ensure they are *outside* the if ($_SERVER['REQUEST_METHOD'] == "POST") block if they are global functions.
                // For brevity in this diff, they are not repeated but are essential.
                // (The existing functions from the previous step are retained here)

                function isOperator($token) {
                    return in_array($token, ['+', '-', '*', '/', '^', '%']);
                }

                function isFunction($token) {
                    return in_array($token, ['sin', 'cos', 'tan', 'asin', 'acos', 'atan', 'log', 'ln', 'sqrt', 'exp', 'negate']); // 'negate' for unary minus
                }

                function isBuiltInConstant($token) {
                    return in_array($token, ['pi', 'e']);
                }

                function getPrecedence($operator) {
                    switch ($operator) {
                        case '^': return 4;
                        case '*': case '/': case '%': return 3;
                        case '+': case '-': return 2;
                        default: return 0; // For parentheses or numbers
                    }
                }

                function isRightAssociative($operator) {
                    return $operator === '^';
                }

                function tokenize($expression) {
                    // Normalize expression: replace "π" and "e"
                    $expression = str_replace('π', 'pi', $expression);
                    $expression = str_replace('√', 'sqrt', $expression); // Assuming sqrt is typed as √ sometimes

                    // Add spaces around operators and parentheses for easier splitting
                    $expression = preg_replace('/([\+\-\*\/\^\(\)\%\√])/', ' $1 ', $expression);
                    $expression = trim(preg_replace('/\s+/', ' ', $expression)); // Remove multiple spaces

                    $tokens = [];
                    $parts = explode(' ', $expression);

                    $lastTokenWasOperatorOrParen = true; // To detect unary minus

                    foreach ($parts as $part) {
                        if ($part === '') continue;

                        if (is_numeric($part)) {
                            $tokens[] = (float)$part;
                            $lastTokenWasOperatorOrParen = false;
                        } elseif (isOperator($part)) {
                            if ($part === '-' && $lastTokenWasOperatorOrParen) { // Unary minus
                                $tokens[] = 'negate'; // Special function for unary minus
                            } else {
                                $tokens[] = $part;
                            }
                            $lastTokenWasOperatorOrParen = true;
                        } elseif (isFunction($part) || isBuiltInConstant($part)) {
                            $tokens[] = $part;
                            $lastTokenWasOperatorOrParen = true; // Functions are followed by '('
                        } elseif ($part === '(' || $part === ')') {
                            $tokens[] = $part;
                            $lastTokenWasOperatorOrParen = true;
                        } else {
                            // Check if it's a function name followed by a number without space like "sin30"
                            // This basic tokenizer doesn't handle that, would need preg_match_all
                            throw new Exception("Invalid token: " . htmlspecialchars($part));
                        }
                    }
                    return $tokens;
                }

                function shuntingYard($tokens) {
                    $outputQueue = [];
                    $operatorStack = [];

                    foreach ($tokens as $token) {
                        if (is_numeric($token)) {
                            $outputQueue[] = $token;
                        } elseif (isBuiltInConstant($token)) {
                            if ($token === 'pi') $outputQueue[] = M_PI;
                            elseif ($token === 'e') $outputQueue[] = M_E;
                        } elseif (isFunction($token)) {
                            $operatorStack[] = $token;
                        } elseif (isOperator($token)) {
                            while (count($operatorStack) > 0 &&
                                   ($topOp = end($operatorStack)) &&
                                   (isFunction($topOp) ||
                                    (isOperator($topOp) && getPrecedence($topOp) > getPrecedence($token)) ||
                                    (isOperator($topOp) && getPrecedence($topOp) == getPrecedence($token) && !isRightAssociative($token))) &&
                                   $topOp !== '(') {
                                $outputQueue[] = array_pop($operatorStack);
                            }
                            $operatorStack[] = $token;
                        } elseif ($token === '(') {
                            $operatorStack[] = $token;
                        } elseif ($token === ')') {
                            while (count($operatorStack) > 0 && ($topOp = end($operatorStack)) !== '(') {
                                $outputQueue[] = array_pop($operatorStack);
                                if (count($operatorStack) == 0) {
                                    throw new Exception("Mismatched parentheses: Unmatched ')'");
                                }
                            }
                            if (count($operatorStack) > 0 && end($operatorStack) === '(') {
                                array_pop($operatorStack); // Pop the '('
                            } else {
                                throw new Exception("Mismatched parentheses: Unmatched ')'");
                            }
                            if (count($operatorStack) > 0 && isFunction(end($operatorStack))) {
                                $outputQueue[] = array_pop($operatorStack);
                            }
                        }
                    }

                    while (count($operatorStack) > 0) {
                        $op = array_pop($operatorStack);
                        if ($op === '(') {
                            throw new Exception("Mismatched parentheses: Unmatched '('");
                        }
                        $outputQueue[] = $op;
                    }
                    return $outputQueue;
                }

                function evaluateRPN($rpnQueue) {
                    $valueStack = [];

                    foreach ($rpnQueue as $token) {
                        if (is_numeric($token)) {
                            $valueStack[] = $token;
                        } elseif (isOperator($token)) {
                            if ($token === '%') { // Unary percentage: X% -> X/100
                                if (count($valueStack) < 1) throw new Exception("Insufficient operands for '%'");
                                $operand = array_pop($valueStack);
                                $valueStack[] = $operand / 100;
                            } else { // Binary operators
                                if (count($valueStack) < 2) throw new Exception("Insufficient operands for operator " . htmlspecialchars($token));
                                $b = array_pop($valueStack);
                                $a = array_pop($valueStack);
                                switch ($token) {
                                    case '+': $valueStack[] = $a + $b; break;
                                    case '-': $valueStack[] = $a - $b; break;
                                    case '*': $valueStack[] = $a * $b; break;
                                    case '/':
                                        if ($b == 0) throw new Exception("Division by zero");
                                        $valueStack[] = $a / $b;
                                        break;
                                    case '^': $valueStack[] = pow($a, $b); break;
                                    // '%' was handled as unary, if binary modulo needed, add here.
                                    default: throw new Exception("Unknown operator: " . htmlspecialchars($token));
                                }
                            }
                        } elseif (isFunction($token)) {
                            // All functions here are unary for simplicity, except if we define multi-arg functions later
                            if (count($valueStack) < 1) throw new Exception("Insufficient operands for function " . htmlspecialchars($token));
                            $arg = array_pop($valueStack);
                            switch ($token) {
                                case 'sin': $valueStack[] = sin(deg2rad($arg)); break; // Assuming degrees input
                                case 'cos': $valueStack[] = cos(deg2rad($arg)); break; // Assuming degrees input
                                // Re-typing the 'tan' case to ensure no hidden characters
                                case 'tan':
                                    if (cos(deg2rad($arg)) == 0.0) { // Explicitly using 0.0 for float comparison
                                        throw new Exception("Tan undefined for 90, 270, etc. degrees");
                                    }
                                    $valueStack[] = tan(deg2rad($arg));
                                    break;
                                case 'asin':
                                    if ($arg < -1 || $arg > 1) throw new Exception("asin domain error: input must be between -1 and 1");
                                    $valueStack[] = rad2deg(asin($arg)); break; // Output in degrees
                                case 'acos':
                                    if ($arg < -1 || $arg > 1) throw new Exception("acos domain error: input must be between -1 and 1");
                                    $valueStack[] = rad2deg(acos($arg)); break; // Output in degrees
                                case 'atan': $valueStack[] = rad2deg(atan($arg)); break; // Output in degrees
                                case 'log': // base 10
                                    if ($arg <= 0) throw new Exception("Log of non-positive number");
                                    $valueStack[] = log10($arg); break;
                                case 'ln': // natural log
                                    if ($arg <= 0) throw new Exception("Ln of non-positive number");
                                    $valueStack[] = log($arg); break; // log() is natural log in PHP
                                case 'sqrt':
                                    if ($arg < 0) throw new Exception("Sqrt of negative number");
                                    $valueStack[] = sqrt($arg); break;
                                case 'exp': $valueStack[] = exp($arg); break;
                                case 'negate': $valueStack[] = -$arg; break;
                                default: throw new Exception("Unknown function: " . htmlspecialchars($token));
                            }
                        }
                    }

                    if (count($valueStack) !== 1) {
                        // This might happen with a malformed RPN or an unhandled case.
                        // Or if the initial expression was just a number that didn't get processed by an op.
                        // If the initial expression is just a number, RPN queue is [number], valuestack becomes [number]
                        // if (count($rpnQueue) === 1 && is_numeric($rpnQueue[0]) && count($valueStack) === 1) return $valueStack[0];
                        throw new Exception("Invalid expression: The final value stack has " . count($valueStack) . " items. Should be 1.");
                    }
                    return $valueStack[0];
                }

            </div>
            <?php
                // The definitions of isOperator, isFunction, getPrecedence, isRightAssociative,
                // tokenize, shuntingYard, and evaluateRPN must be present here, outside the
                // main if/else logic for POST request handling but within PHP tags.
                // (These functions are assumed to be here from the prior step)
            ?>
        </div>
        <div class="calculator-footer">
            <p>&copy; <?php echo date("Y"); ?> Scientific Calculator</p>
        </div>
    </div>
    <script src="js/script.js" defer></script>
</body>
</html>
