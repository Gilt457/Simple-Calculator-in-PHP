document.addEventListener('DOMContentLoaded', () => {
    const displayInput = document.querySelector('input[name="display"]');
    const expressionInput = document.querySelector('input[name="expression"]');
    const calculatorForm = document.querySelector('.calculator-body form');

    // Initialize display if it's empty or not set by PHP (e.g. on first load)
    // PHP already sets displayInput.value to '0' or previous result,
    // and expressionInput.value to previous expression or empty.
    // So, JS initialization might only be for truly fresh loads if PHP didn't output value="0".
    if (displayInput.value === '' && expressionInput.value === '') {
        displayInput.value = '0';
    }

    calculatorForm.addEventListener('click', (event) => {
        const target = event.target;
        // Only react if a button was clicked
        if (!target.matches('button')) {
            return;
        }

        // Prevent default form submission for all buttons except 'equals'
        // The 'equals' button is type="submit" and will submit the form.
        if (!target.classList.contains('btn-equals')) {
            event.preventDefault();
        }

        const buttonValue = target.value; // `value` attribute from HTML
        const buttonText = target.textContent.trim(); // Text content of button
        let currentDisplay = displayInput.value;
        // expressionInput.value is managed to be what PHP expects

        // Handle initial '0' in display
        if (currentDisplay === '0' && !isOperator(buttonValue) && buttonValue !== '.' && !isFunction(buttonValue) && buttonValue !== '(' && buttonValue !== '±' && buttonValue !== '1/x' && buttonValue !== '%') {
            if(buttonValue !== '0'){ // don't allow '00'
                 currentDisplay = '';
            } else if (buttonValue === '0' && currentDisplay === '0') {
                // do nothing if current is 0 and 0 is pressed
                return;
            }
        }

        // AC (All Clear)
        if (target.classList.contains('btn-clear')) {
            displayInput.value = '0';
            expressionInput.value = '';
            return;
        }

        // DEL (Delete/Backspace)
        if (target.classList.contains('btn-delete')) {
            if (currentDisplay.length > 1) {
                // More complex logic needed if display and expression diverge significantly (e.g. sin() vs sin)
                // For now, simple string slice for both
                let newDisplay = currentDisplay.slice(0, -1);
                // Check if last part was a function like 'sin(' 'cos(' etc.
                const funcMatch = newDisplay.match(/(sin|cos|tan|asin|acos|atan|log|ln|sqrt|negate)\($/i);
                if (funcMatch) {
                    newDisplay = newDisplay.slice(0, -funcMatch[1].length -1); // remove function name and (
                }
                displayInput.value = newDisplay;
                expressionInput.value = newDisplay; // Assume expression mirrors display for DEL simplicity
            } else {
                displayInput.value = '0';
                expressionInput.value = '';
            }
            return;
        }

        // Equals button: Let form submit naturally. Expression is already built.
        if (target.classList.contains('btn-equals')) {
            // Ensure the hidden expression field is up-to-date with the display
            // before natural form submission.
            expressionInput.value = displayInput.value;
            // No event.preventDefault() was called, so form will submit.
            return;
        }

        // Memory Functions - simplified
        if (target.classList.contains('btn-memory')) {
            const memoryOp = buttonText; // MC, MR, M+, M-
            let memExpression = '';
            switch(memoryOp) {
                case 'MC': memExpression = 'memory_clear'; break;
                case 'MR': memExpression = 'memory_recall'; break;
                case 'M+': memExpression = 'memory_add:' + currentDisplay; break; // PHP needs to parse this
                case 'M-': memExpression = 'memory_subtract:' + currentDisplay; break; // PHP needs to parse this
            }
            expressionInput.value = memExpression;
            // Display a message or submit. For now, we assume user hits equals or another op to process.
            // To make it immediate, we would submit the form:
            // calculatorForm.submit();
            // For now, let's just update the expression and let user continue or press equals.
            // A better UX might involve AJAX or specific display updates here.
            // For this subtask, setting expressionInput is sufficient.
            // Let's provide some feedback on display for MC and MR
            if (memoryOp === 'MC') displayInput.value = 'Mem Cleared';
            if (memoryOp === 'MR') displayInput.value = 'Mem Recall'; // PHP will fill this via result
            return;
        }


        // Building Display and Expression
        if (target.classList.contains('btn-number')) {
            displayInput.value = currentDisplay + buttonValue;
        } else if (target.classList.contains('btn-operator')) {
            // Add spaces around operators for easier parsing on server if needed,
            // but PHP tokenizer handles it. For display, direct append is fine.
            // Avoid multiple operators: 1* / 5 -> needs handling
            // For simplicity now, just append.
            displayInput.value = currentDisplay + buttonValue;
        } else if (target.classList.contains('btn-function')) {
            // Functions like sin, cos, log, sqrt, ^, %, 1/x, ±, pi, e
            const func = buttonValue;
            if (func === 'x<sup>y</sup>' || func === '^') { // x<sup>y</sup> is display, ^ is value
                 displayInput.value = currentDisplay + '^';
            } else if (func === '√' || func === 'sqrt') {
                 displayInput.value = currentDisplay + 'sqrt(';
            } else if (func === '±') {
                // This is a tricky one. Simplest: wrap last number with negate() or prepend negate(
                // For now, let's just prepend 'negate(' to the current expression.
                // A more robust solution would identify the last number.
                displayInput.value = currentDisplay + 'negate(';
                // User needs to add the number and closing ')'
            } else if (func === '1/x') {
                displayInput.value = currentDisplay + '1/';
                // User needs to add the number. Could also be 1/(currentNumber)
            } else if (func === '%') {
                 displayInput.value = currentDisplay + '%';
            } else if (func === 'pi' || func === 'e') { // Constants
                 displayInput.value = currentDisplay + buttonText; // π or e
            } else { // Standard functions like sin, cos, log, ln, (, )
                 displayInput.value = currentDisplay + func + '(';
            }
        } else if (buttonValue === '.') {
            // Prevent multiple decimal points in the current number segment
            const currentSegments = currentDisplay.split(/[\+\-\*\/\^\(\)%]/);
            if (!currentSegments[currentSegments.length - 1].includes('.')) {
                displayInput.value = currentDisplay + '.';
            }
        } else if (buttonValue === '(' || buttonValue === ')') {
             displayInput.value = currentDisplay + buttonValue;
        }

        // Always update the hidden expression field to mirror the display for this simplified version.
        // More complex logic might be needed if display and expression formats need to diverge significantly.
        expressionInput.value = displayInput.value;
    });

    // Helper functions (can be expanded)
    function isOperator(value) {
        return ['+', '-', '*', '/', '^'].includes(value);
    }
    function isFunction(value){
        return ['sin', 'cos', 'tan', 'asin', 'acos', 'atan', 'log', 'ln', 'sqrt', 'negate', 'pi', 'e', '(', ')', '1/x', '±', '%', '^'].includes(value) || value === 'x<sup>y</sup>' || value === '√';
    }

});
// End of script.js
