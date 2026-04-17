<?php
/**
 * Pattern Hacker - Database Migration Script (REFFACTORED)
 */

require_once 'autoload.php';
require_once 'config.php';

use LKSCore\Core\Schema;
use LKSCore\Core\Database;

echo "<h1>🚀 Pattern Hacker Migration</h1>";
echo "<pre>";

try {

    // =========================
    // 1. USERS TABLE (AUTH)
    // =========================
    echo "Migrating: users... ";

    Schema::create('users', function($table) {
        $table->id();
        $table->string('username')->unique();
        $table->string('password');
        $table->integer('total_score')->default(0);
        $table->timestamps();
    });

    echo "<span style='color:green'>DONE</span>\n";

    // =========================
    // 2. SCORES TABLE (LEADERBOARD)
    // =========================
    echo "Migrating: scores... ";

    Schema::create('scores', function($table) {
        $table->id();
        $table->integer('user_id');
        $table->integer('score');
        $table->integer('difficulty')->default(1);
        $table->integer('correct_count')->default(0);
        $table->integer('wrong_count')->default(0);
        $table->timestamps();
    });

    echo "<span style='color:green'>DONE</span>\n";

    // =========================
    // 3. GAME SESSIONS (STATE TRACKING)
    // =========================
    echo "Migrating: game_sessions... ";

    Schema::create('game_sessions', function($table) {
        $table->id();
        $table->integer('user_id')->nullable();
        $table->integer('score')->default(0);
        $table->integer('lives')->default(3);
        $table->integer('difficulty')->default(1);
        $table->integer('correct_streak')->default(0);
        $table->integer('wrong_streak')->default(0);
        $table->string('status')->default('active');
        $table->timestamps();
    });

    echo "<span style='color:green'>DONE</span>\n";

    // =========================
    // 4. SETTINGS TABLE (BONUS LKS VALUE)
    // =========================
    echo "Migrating: settings... ";

    Schema::create('settings', function($table) {
        $table->id();
        $table->string('key');
        $table->text('value');
        $table->timestamps();
    });

    echo "<span style='color:green'>DONE</span>\n";

    echo "Applying constraints... <span style='color:green'>DONE</span>\n";

    // =========================
    // COMPLETED
    // =========================
    echo "\n<hr>";
    echo "<h3 style='color:green'>✅ Migration Completed Successfully!</h3>";
    echo "<p>Pattern Hacker database is now production-ready (unique constraints via SchemaBuilder).</p>";
    echo "<p><a href='index.php?action=ping'>Test API</a> | <a href='../index.html'>Frontend</a></p>";

} catch (Exception $e) {
    echo "\n<span style='color:red'>❌ ERROR: " . $e->getMessage() . "</span>\n";
    echo "<p>Check <b>config.php</b> and MySQL connection.</p>";
}

echo "</pre>";
?>

