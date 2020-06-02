<?php

namespace chat\send\Rest;

class MessageService
{
    // Возвращает описание доступных API методов
    public static function getDescription(): array
    {
        $methods = [];

        // Имя методов может быть произвольным но принято использовать следующий формат "имя_разрешения.имя_типа.действие"
        $methods['chat.send.message.get'] = [];

        // Обработчик метода PHP псевдо-тип callback. Анонимные функции пока не поддерживаются.
        $methods['chat.send.message.get']['callback'] = [static::class, 'get'];

        // Не совсем понятно что это и для чего используется
        // Смог найти только что если передать ключ "private" => true то вроде как метод будет считаться приватным
        $methods['chat.send.message.get']['options'] = [];

        return $methods;
    }

    public static function get(array $query, int $start, \CRestServer $server)
    {
        switch ($query['key']){
            case md5('библиотека26585'):
                $userid = 26585;
                break;//проверяем пользователя библиотеки            
            default:
                return ['status' => 'error','code'=>'24', 'message' => "no admission"];
        }

        if (empty($query['miraid'])){
            return ['status' => 'error','code'=>'20','message' => "miraID null"];
        }
        if (empty($query['text'])){
            return ['status' => 'error','code'=>'22','message' => "text null"];
        }
		// фильтр по заданному критерию пользователя 
        $filter = Array
        (
            "UF_MIRA_ID"=> $query['miraid']
        );
        $rsUsers = \CUser::GetList(($by="id"), ($order="desc"), $filter)->Fetch();
        
        if ($rsUsers) {
            switch ($query['option']) {
                case 'chat':
                    $rezultChat = static:: SendChat($rsUsers['ID'], $query['text'], $userid);
                    return ['status' => 'ok','code'=>'10', 'message' => "chat message sent"];
                    break;
                case 'social':
                    $rezultSocialNetwork = static:: SendSocialNetwork($rsUsers['ID'], $query['text'], $query['title'],$userid);
                    if ($rezultSocialNetwork) {
                        return ['status' => 'ok','code'=>'11', 'message' => "SocialNetwork message sent"];
                    } else {
                        return ['status' => 'error','code'=>'23', 'message' => "SocialNetwork error"];
                    }
                    break;
                default:
                    $rezultChat = static :: SendChat($rsUsers['ID'], $query['text'], $userid);
                    $rezultSocialNetwork = static :: SendSocialNetwork($rsUsers['ID'], $query['text'], $query['title'], $userid);
                    if ($rezultSocialNetwork) {
                        return ['status' => 'ok','code'=>'12', 'message' => "Chat and SocialNetwork message sent"];
                    } else {
                        return ['status' => 'error','code'=>'23', 'message' => "SocialNetwork error"];
                    }
            }
        }
        return ['status' => 'error','code'=>'21','message' => "miraID not found"];
    }

    public static function SendChat($ID,$text,$USERID)
    {
        if ($text=="") $text_title="Пустое сообщение!";
        if (\Bitrix\Main\Loader::includeModule('im')) {
            return \CIMMessage::Add(array(
                'FROM_USER_ID' => $USERID, //от кого
                'TO_USER_ID' => $ID, //кому
                'MESSAGE' => $text,
            ));
        };
    }

    public static function SendSocialNetwork($ID,$text_detail,$text_title,$USERID)
    {
        if ($text_title=="") $text_title="Уважаемый Студент!";
        if ($text_detail=="") $text_title="Пустое сообщение!";
        \CModule::IncludeModule("socialnetwork");
        if(!\CModule::IncludeModule("blog")) die();
        // id пользователя, от которого осуществляется рассылка
        $userId = $USERID;
        // id пользователя, которому осуществляется рассылка
        
        $fromuserId[] = 'U'.$ID;
        $groupuserID[]= $ID;
        global $APPLICATION;
        
        $arBlog = \CBlog::GetList(array(), array('OWNER_ID'=>$userId))->Fetch();
        $arFields = array(
            "TITLE" => $text_title,
            "DETAIL_TEXT_TYPE" => 'text',
            "DETAIL_TEXT" => $text_detail,
            "DATE_PUBLISH" => (new \DateTime())->format('d.m.Y H:m:s'),
            "PUBLISH_STATUS" => 'P',
            "ENABLE_COMMENTS" => 'N',
            "CATEGORY_ID" => '',
            "PATH" => '/company/personal/user/'.$userId.'/blog/#post_id#/',
            "MICRO" => 'N',
            "SOCNET_RIGHTS" => $fromuserId,
            "=DATE_CREATE" => 'now()',
            "AUTHOR_ID" => $userId,
            "BLOG_ID" => $arBlog['ID'],

        );
        $newID = \CBlogPost::Add($arFields);
        if(IntVal($newID)>0)
        {
            $arFields["ID"] = $newID;
            $arParamsNotify = array(
                "bSoNet"=>true,
                'UserID'=>$userId,
                'user_id'=>$userId,
                'SOCNET_GROUP_ID'=>$groupuserID,
                'PATH_TO_POST'=>'/company/personal/user/#user_id#/blog/'.$newID.'/',
            );
            $notify = \CBlogPost::Notify($arFields, $arBlog, $arParamsNotify);
            return "true";
        }
        else
        {
            if ($ex = $APPLICATION->GetException())
                return "false";
        }
    }

}
