<?php
session_start(); // Start the session to store username and start_time

include "conn.php";

// Handle leaderboard clearing
if (isset($_POST['clear_leaderboard'])) {
    $conn->query("DELETE FROM leaderboard");
    header("Location: index.php");
    exit;
}

// Reset session for a new quiz (except leaderboard data)
if (isset($_POST['reset_quiz'])) {
    session_unset(); // Clear session data except for leaderboard
    header("Location: index.php");
    exit;
}

// Check if username is set
if (!isset($_SESSION['user_name'])) {
    // If the username is not set, show the form to enter the username
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
        $_SESSION['user_name'] = htmlspecialchars($_POST['user_name']); // Store username in session
        $_SESSION['start_time'] = microtime(true); // Set start time
        header("Location: index.php"); // Redirect to start the quiz
        exit;
    } else {
        // Display username input form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Enter Your Name</title>
        </head>
        <body>
            <h1>Welcome to <?php echo "PHP Quiz"; ?></h1>
            <form action="" method="post">
                <label for="user_name">Enter your name:</label>
                <input type="text" id="user_name" name="user_name" required>
                <button type="submit">Start Quiz</button>
            </form>
        </body>
        </html>
        <?php
        exit;
    }
}

// Quiz questions and initialization
$quiz_title = "PHP Quiz";
$questions = [
    [
        "question" => "1.  How many bones are in the human body?",
        "options" => ["200", "206", "207", "210"],
        "answer" => "206",
    ],
    [
        "question" => "2. What is the largest mammal on Earth?",
        "options" => ["Blue whale", "Elephant", "Killer whale", "None of the Above"],
        "answer" => "Blue whale",
    ],
    [
        "question" => "3. What food never spoils?",
        "options" => ["Honey", "Cinnamon", "Corned Beef", "Egg"],
        "answer" => "Honey",
    ]
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['reset_quiz']) && !isset($_POST['clear_leaderboard'])) {
    // End time and calculate time taken
    $end_time = microtime(true);
    $start_time = $_SESSION['start_time'];
    $time_taken = round($end_time - $start_time, 2); // Calculate time in seconds

    // Calculate score
    $score = 0;
    foreach ($questions as $index => $question) {
        if (isset($_POST['question' . $index]) && $_POST['question' . $index] == $question['answer']) {
            $score++;
        }
    }

    // Save user score and time into the leaderboard
    $user_name = $_SESSION['user_name']; // Get the username from session
    $stmt = $conn->prepare("INSERT INTO leaderboard (user_name, score, time_taken) VALUES (?, ?, ?)");
    $stmt->bind_param("sii", $user_name, $score, $time_taken);
    $stmt->execute();

    // Display results and leaderboard
    echo "<h2>Your Score: $score/" . count($questions) . "</h2>";
    echo "<p>Time Taken: $time_taken seconds</p>";

    echo "<h3>Leaderboard</h3>";
    $result = $conn->query("SELECT * FROM leaderboard ORDER BY score DESC, time_taken ASC LIMIT 10");

    echo "<table border='1'>";
    echo "<tr><th>Rank</th><th>User</th><th>Score</th><th>Time</th></tr>";
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$rank}</td><td>{$row['user_name']}</td><td>{$row['score']}</td><td>{$row['time_taken']} seconds</td></tr>";
        $rank++;
    }
    echo "</table>";

    // Try Again button: Reset session data but keep leaderboard and username
    echo "<form action='' method='post' style='display:inline;'>
            <button type='submit' name='reset_quiz'>TRY AGAIN?</button>
          </form>";
    echo "<form action='' method='post' style='display:inline;'>
            <button type='submit' name='clear_leaderboard'>Clear Leaderboard</button>
          </form>";

    exit; // Stop execution to avoid reloading the form
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP QUIZ</title>
</head>
<body>
    <h1><?php echo $quiz_title; ?></h1>

    <form action="" method="post">
        <?php foreach ($questions as $index => $question): ?>
        <fieldset>
            <legend><?php echo $question['question']; ?></legend>
            <?php foreach ($question['options'] as $option): ?>
                <label>
                    <input type="radio" name="question<?php echo $index; ?>" value="<?php echo $option; ?>">
                    <?php echo $option; ?>
                </label><br>
            <?php endforeach; ?>
        </fieldset>
        <?php endforeach; ?>

        <br>
        <input type="submit" value="Submit">
    </form>
</body>
</html>
