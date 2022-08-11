<?php namespace GemFourMedia\GFAQs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGfaqsQuestion extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gfaqs_question', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->integer('category_id')->nullable()->unsigned();
            $table->integer('user_id')->nullable()->unsigned();
            $table->string('question', 255);
            $table->string('slug', 255);
            $table->text('answer')->nullable();
            $table->boolean('published')->default(1);
            $table->integer('sort_order')->default(1);
            $table->boolean('featured')->default(0);
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('gemfourmedia_gfaqs_question');
    }
}
