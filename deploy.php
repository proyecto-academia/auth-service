<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

set('repository', 'https://github.com/proyecto-academia/auth-service.git');

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('13.61.92.244')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/auth-service');

// Hooks

after('deploy:failed', 'deploy:unlock');
