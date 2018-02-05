<?php
/**
 * Created by PhpStorm.
 * User: gadflybsd
 * Date: 2018/1/25
 * Time: 下午2:51
 */

namespace app\api\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;

class Test extends Command{
	protected function configure(){
		$this->setName('test')->setDescription('Here is the remark ');
	}

	protected function execute(Input $input, Output $output){
		//获取参数值
		$args = $input->getArguments();
		$output->writeln('The args value is:');
		print_r($args);

		//获取选项值
		$options = $input->getOptions();
		$output->writeln('The options value is:');
		print_r($options);

		$output->writeln('Now execute command...');

		$output->writeln("End..");
	}
}