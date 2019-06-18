<?php

namespace eCreeth\Relationships;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RelationshipsCommand extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'make:relationship';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create model relationships';

  /**
   * Relationship type.
   *
   * @var string
   */
  private $type = '';

  /**
   * The parent model instance of the relationship.
   *
   * @var string
   */
  private $parent = '';

  /**
   * The child model instance of the relationship.
   *
   * @var string
   */
  private $child = '';

  /**
   * The $farParent
   *
   * @var string
   */
  private $farParent = '';

  /**
   * The $throughChild
   *
   * @var string
   */
  private $throughChild = '';
  
  /**
   * The polymorphicName
   *
   * @var string
   */
  private $polymorphicName = '';
  
  /**
   * The polymorphicCant
   *
   * @var string
   */
  private $polymorphicCant = '';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Prompt the user for input.
   *
   * @param  string  $question
   * @param  string|null  $default
   * @return mixed
   */
  public function ask($question, $default = null)
  {
    return $this->cleanModelName($this->output->ask($question, $default));
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $this->type = $this->choice(
      'Choose the type of relationship',
      [
        'One To One',
        'One To Many',
        'Many To Many',
        'Has One Through',
        'Has Many Through',
        'One To One ( Polymorphic )',
        'One To Many ( Polymorphic )',
        'Many To Many ( Polymorphic )'
      ],
      0
    );
    $this->verifyRelationType();
  }

  private function verifyRelationType()
  {
    switch ($this->type) {
      case 'One To One':

        $this->comment("| A one-to-one relationship is a very basic relation. For example, a User model might be associated with one Phone.");
        $this->askForParentAndChildModels();

        $this->addRelation('one-to-one');
        $this->addInverseRelation('one-to-one-inverse');
        
        $this->relationshipWasCreated();
        $this->warn("\n| Eloquent determines the foreign key of the relationship based on the model name.\n| In this case, the {$this->child} model is automatically assumed to have a " . Str::lower($this->parent) . "_id foreign key.\n| If you wish to override this convention, you may pass a second argument to the hasOne method in the {$this->parent} model.");
        break;

      case 'One To Many':

        $this->comment("| A one-to-many relationship is used to define relationships where a single model owns any amount of other models.\n| For example, a blog post may have an infinite number of comments.");
        $this->askForParentAndChildModels();

        $this->addRelation('one-to-many', true);
        $this->addInverseRelation('one-to-one-inverse');

        $this->relationshipWasCreated();
        $this->warn("\n| Eloquent determines the foreign key of the relationship based on the model name.\n| In this case, the {$this->child} model is automatically assumed to have a " . Str::lower($this->parent) . "_id foreign key.\n");
        break;  

      case 'Many To Many':

        $this->comment("| A many-to-many relationship is used to define relationships in which a model has any number of other models.\n| For example, many users may have the role of \"Admin\" and the admin role may belongs to many users.\n| To define this relationship, three database tables are needed: users, roles, and role_user. \n| The role_user table is derived from the alphabetical order of the related model names, and contains the user_id and role_id columns.");

        $this->parent = $this->ask('What is the first model name instance of the relationship?');
        $this->child  = $this->ask('What is the second model name instance of the relationship?');

        $this->addRelation('many-to-many', true);
        $this->addInverseRelation('many-to-many', true);

        $this->relationshipWasCreated();
        $this->warn("\n| Make sure that the pivot table contains the columns of \"".Str::lower($this->parent)."_id\" & \"".Str::lower($this->child)."_id\"");
        break;

      case 'Has One Through':
      
        $this->comment("| The has-one-through relationship links models through a single intermediate relation.\n| For example, if each \"supplier\" (AS ACCESSOR) has one \"user\" (AS INTERMATE MODEL), and each user is associated\n| with one user \"history\" (AS A MODEL THAT WE WANT TO ACCESS) record,\n| then the supplier model may access the user's history through the user.");

        $this->askForThroughModelNames();

        $this->addThroughRelation();
        $this->relationshipWasCreated();
        $this->warnForThroughRelations();
        break;

      case 'Has Many Through':

        $this->comment("| The \"has-many-through\" relationship provides a convenient shortcut for accessing distant relations via an intermediate relation.\n| For example, a \"Country\" model might have many \"Post\" models through an intermediate \"User\" model.\n| In this example, you could easily gather all blog posts for a given country.");
        $this->askForThroughModelNames();

        $this->addThroughRelation('has-many-through');
        $this->relationshipWasCreated();
        $this->warnForThroughRelations();
        break;

      case 'One To One ( Polymorphic )':

        $this->askForPolymorphicModelNames();

        for ($i = 1; $i <= $this->polymorphicCant; $i++) {
          $modelName  = $this->ask("Model name number {$i}");
          $this->addPolymorphicOneRelation($modelName);
        }
        $this->addPolymorphicToRelation();
        $this->relationshipWasCreated();
        break;

      case 'One To Many ( Polymorphic )':

        $this->askForPolymorphicModelNames();

        for ($i = 1; $i <= $this->polymorphicCant; $i++) {
          $modelName  = $this->ask("Model name number {$i}");
          $this->addPolymorphicOneRelation($modelName, 'morphMany', true);
        }
        $this->addPolymorphicToRelation();
        $this->relationshipWasCreated();
        break;

      case 'Many To Many ( Polymorphic )':

        $this->comment("A blog Post and Video model could share a polymorphic relation to a Tag model. Using a many-to-many polymorphic relation \nallows you to have a single list of unique tags that are shared across blog posts and videos.");
        $this->askForPolymorphicModelNames();

        for ($i = 1; $i <= $this->polymorphicCant; $i++) {
          $modelName  = $this->ask("Model name number {$i}");
          $this->addManyToManyPolymorphic($modelName);
          $this->addInverseManyToManyPolymorphic($modelName);
        }

        $this->relationshipWasCreated();
        break;

      default:
        return 'not found';
        break;
    }
  }
  // 
  private function warnForThroughRelations()
  {
    $this->warn("| Make sure that the \"{$this->parent}\" model has the foreign key \"".Str::lower($this->farParent)."_id\" and the \"{$this->throughChild}\" model has the foreign key \"".Str::lower($this->parent)."_id\"");
  }
  // 
  private function relationshipWasCreated()
  {
    $this->info("The relationship \"{$this->type}\" was created");
  }
  //
  private function askForPolymorphicModelNames()
  {
     $this->polymorphicName  = $this->ask('Polymorphic Model Name');
     $this->polymorphicCant  = parent::ask('Model count for this polimorphic relationship?');
  }
  // 
  private function askForThroughModelNames()
  {
    $this->farParent    = $this->ask('What is the accessor model name of relationship?');
    $this->parent       = $this->ask('What is the name of the intermediate model of the relationship?');
    $this->throughChild = $this->ask('What is the name of the model you want to access?');
  }
  // 
  private function askForParentAndChildModels()
  {
    $this->parent = $this->ask('What is the parent model instance of the relationship?');
    $this->child  = $this->ask('What is the child model instance of the relationship?');
  }
  ///////////////
  private function addInverseManyToManyPolymorphic(string $modelName)
  {
    $path = app_path("{$this->polymorphicName}.php");
    File::append(
      $path,
      $this->replaceInverseManyToManyPolymorphicNames($modelName)
    );
  }
  private function replaceInverseManyToManyPolymorphicNames(string $modelName)
  {
    $modelFunName    = Str::plural(Str::lower($modelName));
    $prefix          = Str::lower($this->polymorphicName) . 'able';

    return str_replace(
      ['MedelFunName', 'ModelName', 'Prefix'],
      [$modelFunName, $modelName, $prefix],
      File::get(__DIR__ . "/stubs/many-to-many-polymorphic-inverse.stub")
    );
  }
  private function addManyToManyPolymorphic(string $modelName)
  {
    $path = app_path("{$modelName}.php");
    File::append(
      $path,
      $this->replaceManyToManyPolymorphicNames()
    );
  }
  private function replaceManyToManyPolymorphicNames()
  {
    $polFucName    = Str::plural(Str::lower($this->polymorphicName));
    $polModelName  = Str::studly($this->polymorphicName);
    $prefix        = Str::lower($this->polymorphicName) . 'able';

    return str_replace(
      ['PolFunName', 'PolModelName', 'Prefix'],
      [$polFucName, $polModelName, $prefix],
      File::get(__DIR__ . "/stubs/many-to-many-polymorphic.stub")
    );
  }
  // ////////////////

  private function cleanModelName(string $name): string
  {
    if (is_numeric($name)) {
      $this->error("The {$name} must be a valid model name");
      exit;
    }
    $name = Str::studly(Str::singular(Str::lower($name)));
    $path = app_path("{$name}.php");

    if (!File::exists($path)) {
      if ($this->confirm("The [{$name}] model does not exists. Do you want to create it?", true)) {
        $this->call('make:model', ['name' => $name]);
      } else {
        $this->warn("Lo sentimos, pero el modelo {$name} debe existir para poder agregarle la relacion");
        exit;
      }
    }
    return $name;
  }

  // /////////////////////////////// One To One ( Polymorphic )
  private function addPolymorphicOneRelation(string $modelName, string $method = 'morphOne', bool $plural = false)
  {
    $path = app_path("{$modelName}.php");
    File::append(
      $path,
      $this->replacePolymorphicOneRelationNames($modelName, $method, $plural)
    );
  }
  private function replacePolymorphicOneRelationNames(string $modelName, string $method, bool $plural)
  {
    $modelName             = Str::lower($modelName);
    $polymorphicName       = $plural ? Str::plural(Str::lower($this->polymorphicName)) : Str::lower($this->polymorphicName);
    $polymorphicModelName  = Str::studly($this->polymorphicName);
    $methodFunName         = $plural ? (Str::lower($this->polymorphicName) . 'able') : $polymorphicName;

    return str_replace(
      ['CurrentModelName', 'PolimorphicFuncName', 'PolimorphicModelName', 'MethodName', 'minable'],
      [$modelName, $polymorphicName, $polymorphicModelName, $method, $methodFunName],
      File::get(__DIR__ . "/stubs/one-to-one-polymorphic-one.stub")
    );
  }
  private function addPolymorphicToRelation()
  {
    $path = app_path("{$this->polymorphicName}.php");
    File::append(
      $path,
      $this->replacePolymorphicRelationNames()
    );
  }
  private function replacePolymorphicRelationNames()
  {
    $polymorphicName = Str::lower($this->polymorphicName);

    return str_replace(
      ['FunctionName'],
      [$polymorphicName],
      File::get(__DIR__ . "/stubs/one-to-one-polymorphic-to.stub")
    );
  }
  // ///////////////////////////////

  private function addThroughRelation(string $stub = 'has-one-through')
  {
    $path = app_path("{$this->farParent}.php");
    File::append(
      $path,
      $this->replaceThroughRelationNames($stub)
    );
  }

  private function replaceThroughRelationNames(string $stub)
  {
    $parent            = Str::studly($this->parent);
    $parentName        = Str::lower($this->parent);
    $throughName       = Str::studly($this->throughChild);

    return str_replace(
      ['parentName', 'ThroughDummyRelationName', 'ThroughDummyModelName', 'parentModelName'],
      [$parentName, $throughName, $throughName, $parent],
      File::get(__DIR__ . "/stubs/{$stub}.stub")
    );
  }
  // ////////////////////////////////////

  private function addInverseRelation(string $stub, bool $plural = false)
  {
    $path = app_path("{$this->child}.php");
    File::append(
      $path,
      $this->replaceRelationNames($stub, $this->parent, $plural)
    );
  }

  private function addRelation(string $stub, bool $plural = false)
  {
    $path = app_path("{$this->parent}.php");
    File::append(
      $path,
      $this->replaceRelationNames($stub, $this->child, $plural)
    );
  }

  private function replaceRelationNames(string $stub, string $modelName, bool $plural = false)
  {
    $modelName      = Str::studly($modelName);
    $namefunction   = $plural ? Str::plural(Str::lower($modelName)) : Str::lower($modelName);

    return str_replace(
      ['DummyModel', 'dummyRelationName'],
      [$modelName, $namefunction],
      File::get(__DIR__ . "/stubs/{$stub}.stub")
    );
  }
}
