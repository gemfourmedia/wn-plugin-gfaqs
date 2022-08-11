<?php namespace GemFourMedia\GFAQs\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateGemfourmediaGfaqsCategory extends Migration
{
    public function up()
    {
        Schema::create('gemfourmedia_gfaqs_category', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->text('desc')->nullable();
            $table->integer('sort_order')->default(1);
            $table->text('params')->nullable();
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
        Schema::dropIfExists('gemfourmedia_gfaqs_category');
    }
}
