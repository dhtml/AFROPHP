<?php
namespace Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

//taking user inputs e.g. bundle name
use Symfony\Component\Console\Question\Question;


//choice questions
use Symfony\Component\Console\Question\ChoiceQuestion;


//table helper
use Symfony\Component\Console\Helper\Table;


class SetName extends Command
{
  protected function configure()
  {
    $this
    // the name of the command (the part after "bin/console")
    ->setName('app:set-name')

    // the short description shown while running "php bin/console list"
    ->setDescription('Sets your name.')

    // the full command description shown when running the command with
    // the "--help" option
    ->setHelp('This command allows you to set your name...');
  }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        //$question = new ConfirmationQuestion('Continue with this action?', false);

        /*
        $question = new ConfirmationQuestion(
            'Continue with this action?',
            false,
            '/^(y|j)/i'
        );


        if (!$helper->ask($input, $output, $question)) {
                $output->writeln('try again later.');
                return;
        }


        $question = new Question('Please enter the name of the bundle? ', 'AcmeDemoBundle');

        $bundle = $helper->ask($input, $output, $question);


        $output->writeln('Your bundlename is now '.$bundle);


        //choice question
        $helper = $this->getHelper('question');
  $question = new ChoiceQuestion(
      'Please select your favorite color (defaults to red)',
      array('red', 'blue', 'yellow'),
      0
  );
  $question->setErrorMessage('Color %s is invalid.');

  $color = $helper->ask($input, $output, $question);
  $output->writeln('You have just selected: '.$color);


  //multiple choice questions
  $helper = $this->getHelper('question');
$question = new ChoiceQuestion(
    'Please select your favorite colors (separate with comas defaults to red and blue)',
    array('red', 'blue', 'yellow'),
    '0,1'
);
$question->setMultiselect(true);

$colors = $helper->ask($input, $output, $question);
$output->writeln('You have just selected: ' . implode(', ', $colors));
*/


//using autocomplete
/*
$bundles = array('AcmeDemoBundle', 'AcmeBlogBundle', 'AcmeStoreBundle');
$question = new Question('Please enter the name of a bundle', 'FooBundle');
$question->setAutocompleterValues($bundles);

$name = $helper->ask($input, $output, $question);
*/

//hiding user response:

/*
$question = new Question('What is the database password?');
   $question->setHidden(true);
   $question->setHiddenFallback(false);

   $password = $helper->ask($input, $output, $question);
*/

//normalize answers

/*
$question = new Question('Please enter the name of the bundle', 'AppBundle');
$question->setNormalizer(function ($value) {
    // $value can be null here
    return $value ? trim($value) : '';
});

$name = $helper->ask($input, $output, $question);
*/

/*
//question validator
$question = new Question('Please enter the name of the bundle', 'AcmeDemoBundle');
 $question->setValidator(function ($answer) {
     if (!is_string($answer) || 'Bundle' !== substr($answer, -6)) {
         throw new \RuntimeException(
             'The name of the bundle should be suffixed with \'Bundle\''
         );
     }

     return $answer;
 });
 $question->setMaxAttempts(2);

 $name = $helper->ask($input, $output, $question);
*/

/*
//validating hidden response
$helper = $this->getHelper('question');

$question = new Question('Please enter your password');
$question->setValidator(function ($value) {
    if (trim($value) == '') {
        throw new \Exception('The password cannot be empty');
    }

    return $value;
});
$question->setHidden(true);
$question->setMaxAttempts(20);

$password = $helper->ask($input, $output, $question);
*/


$table = new Table($output);
$table
    ->setHeaders(array('ISBN', 'Title', 'Author'))
    ->setRows(array(
        array('99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'),
        array('9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'),
        array('960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'),
        array('80-902734-1-6', 'And Then There Were None', 'Agatha Christie'),
    ))
;
$table->render();

    }
}
