<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('authorization_codes', function (Blueprint $table) {
            $table->string('code');
            $table->string('client_id', 2000);
            $table->string('redirect_uri', 2000);
            $table->string('code_challenge');
            $table->string('code_challenge_method');
            $table->timestamp('expiration');
            $table->string('scopes');
            $table->boolean('used');
            $table->boolean('refresh');
            $table->foreignId('user_id');
            $table->foreign("user_id")->references("id")->on("users");
            $table->primary('code');
        });

        Schema::create('tokens', function (Blueprint $table) {
            $table->string('code');
            $table->string('type');
            $table->timestamp('expiration');
            $table->string('scopes');
            $table->string('client_id', 2000);
            $table->foreignId('user_id');
            $table->foreign("user_id")->references("id")->on("users");
            $table->primary('code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('authorization_codes');
    }
};
