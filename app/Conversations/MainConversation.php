<?php

namespace App\Conversations;

use Illuminate\Foundation\Inspiring;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Conversations\Conversation;
use BotMan\BotMan\Messages\Outgoing\OutgoingMessage;
use BotMan\BotMan\Messages\Attachments\Image;
use App\Conversations\MenuConversation;
use App\WarGames;
use App\RoleGames;
use App\BoardGames;
use App\BoardGameGenre;
use App\WarGamePurchases;
use App\RoleGamePurchases;
use App\BoardGamePurchases;

class MainConversation extends Conversation
{
    public function askHello()
    {
        $question = Question::create('Добро пожаловать! Вы пользуетесь ботом хобби-центра "РИО".')
            ->fallback('Unable to ask')
            ->callbackId('hello')
            ->addButtons([
                Button::create('Меню магазина')->value('menu'),
                Button::create('О хобби-центре')->value('centre'),
                Button::create('О приложении')->value('app'),
                Button::create('Закончить разговор')->value('end'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'menu') {
                    $this->showMenu();
                }
                if ($answer->getValue() === 'centre') {
                    $this->aboutCentre();
                }
                if ($answer->getValue() === 'app') {
                    $this->aboutApp();
                }
                if ($answer->getValue() === 'end') {
                    $this->bot->reply('Будем вас ждать!');
                }
            }
        });
    }

    public function aboutCentre()
    {
        $question = Question::create('Хобби-центр "РИО" - это место для развлечений и творчества, где ты можешь поиграть в настольные игры, ролевые игры, Warhammer 40000, Dungeons&Dragons, посмотреть фильмы или заняться любимым хобби. Всё это и многое другое находится в самом центре Донецка!')
            ->fallback('Unable to ask')
            ->callbackId('about_centre')
            ->addButtons([Button::create('Назад')->value('back')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'back') {
                    $this->askHello();
                }
            }
        });
    }

    public function aboutApp()
    {
        $question = Question::create('В данном приложении можно записаться на варгейм, ролевую игру и арендовать настольную игру')
            ->fallback('Unable to ask')
            ->callbackId('about_app')
            ->addButtons([
                Button::create('Q&A')->value('q_and_a'),
                Button::create('Назад')->value('back'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'q_and_a') {
                    $this->questions_answers();
                }
                if ($answer->getValue() === 'back') {
                    $this->askHello();
                }
            }
        });
    }

    public function questions_answers()
    {
        $question = Question::create(
'Q: На сколько арендуются ностольные игры?
A: На 7 дней.
Q: Какой залог при взятии в аренду?
A: 50% стоимости игры.')
            ->fallback('Unable to ask')
            ->callbackId('q_and_a')
            ->addButtons([Button::create('Назад')->value('back')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'back') {
                    $this->aboutApp();
                }
            }
        });
    }

    // Menu

    public function showMenu()
    {
        $question = Question::create('Вы в меню магазина.')
            ->fallback('Unable to ask')
            ->callbackId('menu')
            ->addButtons([
                Button::create('Варгеймы')->value('war_games'),
                Button::create('Настольные игры')->value('board_games'),
                Button::create('Ролевые игры')->value('role_games'),
                Button::create('Покупки')->value('purchases'),
                Button::create('Назад')->value('back'),
            ]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'war_games') {
                    $this->showWarGames();
                }
                if ($answer->getValue() === 'board_games') {
                    $this->showCategoriesBoardGames();
                }
                if ($answer->getValue() === 'role_games') {
                    $this->showRoleGames();
                }
                if ($answer->getValue() === 'purchases') {
                    $this->showPurchases();
                }
                if ($answer->getValue() === 'back') {
                    $this->askHello();
                }
            }
        });
    }

    public function showWarGames($page=1)
    {
        $war_games = WarGames::orderBy('name')->get();
        $this->bot->reply('Список всех варгеймов.');
        $type_element = 'war_game';
        $this->showPagePaginate($page, $war_games, $type_element);
    }

    public function showRoleGames($page=1)
    {
        $role_games = RoleGames::orderBy('name')->get();
        $this->bot->reply('Список всех ролевых игр.');
        $type_element = 'role_game';
        $this->showPagePaginate($page, $role_games, $type_element);
    }

    public function showCategoriesBoardGames($page=1)
    {
        $genre_games = BoardGameGenre::orderBy('name')->get();
        $this->bot->reply('Список всех категорий настольных игр.');
        $type_element = 'board_game_genre';
        $this->showPagePaginate($page, $genre_games, $type_element);
    }

    public function showBoardGames($id_genre, $page=1)
    {
        $board_games = BoardGames::where('genre_id', $id_genre)->orderBy('name')->get();
        $genre = BoardGameGenre::find($id_genre);
        $this->bot->reply('Список всех настольных игр по жанру - ' . $genre->name . '.');
        $type_element = 'board_game';
        $this->showPagePaginate($page, $board_games, $type_element);
    }

    public function showPurchases()
    {
        $this->bot->reply('Ваши записи на варгеймы, ролевые игры и аренды настольных игр.');

        $user_id = $this->bot->getUser()->getId();
        $war_game_purchases = WarGamePurchases::where('user_id', $user_id)->get();
        $role_game_purchases = RoleGamePurchases::where('user_id', $user_id)->get();
        $board_game_purchases = BoardGamePurchases::where('user_id', $user_id)->get();

        $list_war_games = 'Список записей на варгеймы:
';
        foreach ($war_game_purchases as $war_game_purchase) {
            $war_game = WarGames::find($war_game_purchase->war_game_id);
            $list_war_games .= $war_game->name . ' - '. $war_game->time . '
';
        }
        $this->bot->reply($list_war_games);

        $list_role_games = 'Список записей на ролевые игры:
';
        foreach ($role_game_purchases as $role_game_purchase) {
            $role_game = RoleGames::find($role_game_purchase->role_game_id);
            $list_role_games .= $role_game->name . ' - '. $role_game->time . '
';
        }
        $this->bot->reply($list_role_games);

        $list_board_games = 'Список аренд настольных игр:
';
        foreach ($board_game_purchases as $board_game_purchase) {
            $board_game = BoardGames::find($board_game_purchase->board_game_id);
            $list_board_games .= $board_game->name . '
';
        }
        $this->bot->reply($list_board_games);

        $question = Question::create('Вернуться в меню?')
            ->fallback('Unable to ask')
            ->callbackId('back_to_menu')
            ->addButtons([Button::create('Да')->value('yes')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'yes') {
                    $this->showMenu();
                }
            }
        });
    }

    public function showPagePaginate($page, $elements, $type_element, $paginate=5)
    {
        $pages = ceil(count($elements) / $paginate);
        $text_question = 'Страница ' . $page . ' из ' . $pages;
        for ($i=0; $i<5; $i++)
        {
            $index = ($page - 1) * 5 + $i;
            if ($index >= count($elements)) {
                break;
            }
            $buttons[] = Button::create($elements[$index]->name)->value($elements[$index]->id);
        }
        if ($page > 1) {
            $buttons[] = Button::create('<< Предыдущая')->value('early');
        }
        if ($page < $pages) {
            $buttons[] = Button::create('Следующая >>')->value('next');
        }
        $buttons[] = Button::create('Назад')->value('back');
        $question = Question::create($text_question)
            ->fallback('Unable to ask')
            ->callbackId('paginate_page')
            ->addButtons($buttons);

        return $this->ask($question, function (Answer $answer) use($page, $elements, $type_element, $paginate) {
            if ($answer->isInteractiveMessageReply()) {

                if ($answer->getValue() === 'early') {
                    $this->showPagePaginate($page - 1, $elements, $type_element, $paginate);
                } elseif ($answer->getValue() === 'next') {
                    $this->showPagePaginate($page + 1, $elements, $type_element, $paginate);
                } elseif ($answer->getValue() === 'back') {
                    $this->showMenu();
                } else {
                    if ($type_element === 'war_game') {
                        $this->showWarGame($answer->getValue(), $page);
                    }
                    if ($type_element === 'role_game') {
                        $this->showRoleGame($answer->getValue(), $page);
                    }
                    if ($type_element === 'board_game_genre') {
                        $this->showBoardGames($answer->getValue());
                    }
                    if ($type_element === 'board_game') {
                        $this->showBoardGame($answer->getValue(), $page);
                    }
                }
            }
        });
    }

    public function showWarGame($id, $page)
    {
        $war_game = WarGames::find($id);
        $image = new Image(asset($war_game->image));
        $message = OutgoingMessage::create($war_game->name . '

' . $war_game->description . '

Эта игра пройдёт ' . $war_game->time . '

Цена записи на игру ' . $war_game->price . ' р.')
            ->withAttachment($image);
        $this->bot->reply($message);

        $question = Question::create('Хотите записаться на игру?')
            ->fallback('Unable to ask')
            ->callbackId('buy_war_game')
            ->addButtons([
                Button::create('Да')->value('yes'),
                Button::create('Назад')->value('back'),
            ]);

        return $this->ask($question, function (Answer $answer) use($id, $page) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'yes') {
                    $this->purchaseWarGame($id);
                }
                if ($answer->getValue() === 'back') {
                    $this->showWarGames($page);
                }
            }
        });
    }

    public function showRoleGame($id, $page)
    {
        $role_game = RoleGames::find($id);
        $image = new Image(asset($role_game->image));
        $message = OutgoingMessage::create($role_game->name . '

' . $role_game->description . '

Эта игра пройдёт ' . $role_game->time . '

Цена записи на игру ' . $role_game->price . ' р.')
            ->withAttachment($image);
        $this->bot->reply($message);

        $question = Question::create('Хотите записаться на игру?')
            ->fallback('Unable to ask')
            ->callbackId('buy_role_game')
            ->addButtons([
                Button::create('Да')->value('yes'),
                Button::create('Назад')->value('back'),
            ]);

        return $this->ask($question, function (Answer $answer) use($id, $page) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'yes') {
                    $this->purchaseRoleGame($id);
                }
                if ($answer->getValue() === 'back') {
                    $this->showRoleGames($page);
                }
            }
        });
    }

    public function showBoardGame($id, $page)
    {
        $board_game = BoardGames::find($id);
        $image = new Image(asset($board_game->image));
        $message = OutgoingMessage::create($board_game->name . '

' . $board_game->description . '

Стоимость аренды игры ' . $board_game->price . ' р. + залог')
            ->withAttachment($image);
        $this->bot->reply($message);

        $question = Question::create('Хотите арендовать на игру?')
            ->fallback('Unable to ask')
            ->callbackId('buy_board_game')
            ->addButtons([
                Button::create('Да')->value('yes'),
                Button::create('Назад')->value('back'),
            ]);

        return $this->ask($question, function (Answer $answer) use($id, $page) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'yes') {
                    $this->purchaseBoardGame($id);
                }
                if ($answer->getValue() === 'back') {
                    $this->showCategoriesBoardGames();
                }
            }
        });
    }

    public function purchaseWarGame($id)
    {
        $purchase = new WarGamePurchases;
        $purchase->user_id = $this->bot->getUser()->getId();
        $purchase->war_game_id = $id;
        $purchase->save();

        $question = Question::create('Вы успешно записались на варгейм!')
            ->fallback('Unable to ask')
            ->callbackId('purchase_war_game')
            ->addButtons([Button::create('В меню магазина')->value('menu')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'menu') {
                    $this->showMenu();
                }
            }
        });
    }

    public function purchaseRoleGame($id)
    {
        $purchase = new RoleGamePurchases;
        $purchase->user_id = $this->bot->getUser()->getId();
        $purchase->role_game_id = $id;
        $purchase->save();

        $question = Question::create('Вы успешно записались на ролевую игру!')
            ->fallback('Unable to ask')
            ->callbackId('purchase_role_game')
            ->addButtons([Button::create('В меню магазина')->value('menu')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'menu') {
                    $this->showMenu();
                }
            }
        });
    }

    public function purchaseBoardGame($id)
    {
        $purchase = new BoardGamePurchases;
        $purchase->user_id = $this->bot->getUser()->getId();
        $purchase->board_game_id = $id;
        $purchase->save();

        $question = Question::create('Вы успешно арендовали игру, можете забрать её в хобби-центре!')
            ->fallback('Unable to ask')
            ->callbackId('purchase_board_game')
            ->addButtons([Button::create('В меню магазина')->value('menu')]);

        return $this->ask($question, function (Answer $answer) {
            if ($answer->isInteractiveMessageReply()) {
                if ($answer->getValue() === 'menu') {
                    $this->showMenu();
                }
            }
        });
    }

    /**
     * Start the conversation
     */
    public function run()
    {
        $this->askHello();
    }
}
