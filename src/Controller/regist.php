<?php
namespace Nyaan\Controller;
use Baguette\Response;

// FIXME: そんな英語はない
class regist
{
    public function action(\Baguette\Application $app, \Teto\Routing\Action $action)
    {
        if ($app->session->get('user_id', ['default' => false])) {
            return new Response\RedirectResponse('/');
        }
        if(isset($_REQUEST['slug'],$_REQUEST['user'],$_REQUEST['password'])){
            $is_daburi = self::isTyouhuku($_REQUEST['slug']);
        }
        if (isset($is_daburi) && $is_daburi){
            return new Response\TwigResponse('regist.tpl.html', [
                'user' => isset($_REQUEST['user']) ? $_REQUEST['user'] : null,
                'is_daburi' => $is_daburi,
            ]);
        }
        else{
            $login = self::regist($_REQUEST['slug'], $_REQUEST['user'], $_REQUEST['password']);
            $app->session->set('user_id', $login['id']);
            $app->session->set('user_slug', $login['slug']);
            $app->session->set('user_name', $login['name']);
            return new Response\RedirectResponse('/');
        }
    }

    private static function isTyouhuku(string $user_name): bool
    {
        // systemは特殊なユーザーなので登録できない
        if (empty($user_name) || $user_name === 'system') {
            return false;
        }

        $user = trim($user_name);
        $query = "SELECT * FROM `users` WHERE `slug` = \"${user}\" ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        return !empty($data);
    }

    private static function regist($slug, $name, $plain_password): array
    {
        $query = "INSERT INTO `users`(`slug`, `name`) VALUES( \"{$slug}\", \"{$name}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();
        
        $id = db()->lastInsertId();
        $password = password_hash($plain_password, PASSWORD_DEFAULT);
        $query = "INSERT INTO `user_passwords` VALUES( {$id}, \"{$password}\" ); ";
        $stmt = db()->prepare($query);
        $stmt->execute();

        return [
            'id' => $id,
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
