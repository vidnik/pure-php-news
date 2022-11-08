create table auth_group
(
    id        int auto_increment
        primary key,
    name      varchar(150)         not null,
    `default` tinyint(1) default 0 not null,
    constraint name
        unique (name)
);

create table auth_permission
(
    id       int auto_increment
        primary key,
    codename varchar(100) not null,
    name     varchar(255) not null,
    constraint codename
        unique (codename)
);

create table auth_group_permissions
(
    id            int auto_increment
        primary key,
    group_id      int not null,
    permission_id int not null,
    constraint auth_group_permissions_auth_group_id_fk
        foreign key (group_id) references auth_group (id)
            on update cascade on delete cascade,
    constraint auth_group_permissions_auth_permission_id_fk
        foreign key (permission_id) references auth_permission (id)
            on update cascade on delete cascade
);

create table auth_user
(
    id       int auto_increment
        primary key,
    password varchar(128) not null,
    username varchar(150) not null,
    email    varchar(254) not null,
    constraint email
        unique (email),
    constraint username
        unique (username)
);

create table auth_user_group
(
    id       int auto_increment
        primary key,
    user_id  int not null,
    group_id int not null,
    constraint auth_user_group_pk
        unique (group_id, user_id),
    constraint auth_user_group_auth_group_id_fk
        foreign key (group_id) references auth_group (id)
            on update cascade on delete cascade,
    constraint auth_user_group_auth_user_id_fk
        foreign key (user_id) references auth_user (id)
            on update cascade on delete cascade
);

create table news_article
(
    id    int auto_increment
        primary key,
    title varchar(255) not null,
    text  text         not null,
    date  date         not null,
    image blob         not null,
    user  int          not null,
    constraint id
        unique (id),
    constraint news_article_auth_user_id_fk
        foreign key (user) references auth_user (id)
);

create table news_category
(
    id          int auto_increment
        primary key,
    name        varchar(30)  not null,
    slug        varchar(100) not null,
    description text         not null,
    constraint id
        unique (id),
    constraint name
        unique (name),
    constraint slug
        unique (slug)
);

create table news_article_category
(
    article_id  int not null,
    category_id int not null,
    constraint news_article_category_news_article_id_fk
        foreign key (article_id) references news_article (id)
            on update cascade on delete cascade,
    constraint news_article_category_news_category_id_fk
        foreign key (category_id) references news_category (id)
            on update cascade on delete cascade
);

create table news_comment
(
    id         int auto_increment
        primary key,
    article_id int                                not null,
    title      varchar(100)                       not null,
    text       text                               not null,
    parent_id  int                                null,
    datetime   datetime default CURRENT_TIMESTAMP not null,
    user       int                                not null,
    constraint news_comment_auth_user_id_fk
        foreign key (user) references auth_user (id)
            on update cascade on delete cascade
);

