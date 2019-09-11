<?php
// Функция поиска редакций нормативного документа
//        Рекурсивная функция

function versions_document_npa($array_graphs,$a_exclude) {
    $all_posts = get_posts(array(
        'numberposts' => -1, // Все записи (без разделения)
        'post_per_page' => -1,
        'post_type' => 'cpt_norm_leg_act_lsg',
        'exclude' => $a_exclude, //Исключаем документы, которые есть в массиве, так как же их посмотрели
    ));
    if ($array_graphs and count($array_graphs)>0) {
        $array_graph = $array_graphs[0];
        foreach ($all_posts as $all_post) {
            global $post; // Устанавливает $post (глобальная переменная - объект поста)
            // Перенаправим полученные данные поста в переменную $post и не в какую другую.
            $post = $all_post;
            setup_postdata( $post );
            $doc_editor_npa_id = $post->ID; // ID документа, перебор всех записей НПА
            $fields_in_post = get_field_objects($post->ID); // Получаем значение полей для текущего ID
            $look_previos = $fields_in_post['acf_vls_links_docs_previos_npa_omsu_lsg']['value']; // Получаем значение поля, в котором хранятся документы, которые меняются данным ID
            if ($look_previos) {
                foreach ($look_previos as $look_previo) { // Ищем документы, которые меняли данный документ
                    if (is_object($look_previo['acf_vls_link_doc_previos_npa_omsu_lsg'])) {
                        $find_npa_doc_id = $look_previo['acf_vls_link_doc_previos_npa_omsu_lsg']->ID;
                        if ($find_npa_doc_id === $array_graph) {
                            $array_graphs[] = $doc_editor_npa_id;
                        }
                    }
                }
            }


        }
        if (!is_array($a_exclude)) {
            $a_exclude = str_split($a_exclude);
        }
        array_push($a_exclude, $array_graph);
        array_splice($array_graphs, 0, 1); //Удаляем 0 элемент массива поиска

        versions_document_npa($array_graphs, $a_exclude);
    }
    else {
        echo '<br>'.'Редакции документа: '.'<br>';
        // $a_exclude - массив ID документов, которые относятся к данной записи
        $versions_document = get_posts(array(
            'numberposts' => -1, // Все записи (без разделения)
            'post_type' => 'cpt_norm_leg_act_lsg',
            'orderby'     => 'date',
            'order'       => 'DESC',
            'include' => $a_exclude, //Исключаем документы, которые есть в массиве, так как же их посмотрели
        ));
        $current_document_id = get_the_ID(); //ID текущей записи
        echo '<ul>';
        foreach ($versions_document as $version_document) {
            echo '<li><a href="'.$version_document->guid.'">'
                .mb_strimwidth($version_document->post_title,0, 50, "...")
                .'</a>';
        }
        echo '</ul>';
        echo 'Исключаемый список: ';
            print_r($a_exclude);
    }
}
