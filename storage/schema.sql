create table auth_group
(
    id        int auto_increment
        primary key,
    name      varchar(150)         not null,
    `default` tinyint(1) default 0 not null,
    constraint name
        unique (name)
);

INSERT INTO newsphp_db.auth_group (id, name, `default`) VALUES (1, 'superuser', 0);
INSERT INTO newsphp_db.auth_group (id, name, `default`) VALUES (2, 'staff', 0);
INSERT INTO newsphp_db.auth_group (id, name, `default`) VALUES (3, 'active', 1);
INSERT INTO newsphp_db.auth_group (id, name, `default`) VALUES (4, 'TESTGROUP', 0);
INSERT INTO newsphp_db.auth_group (id, name, `default`) VALUES (7, 'Test group 123', 0);

create table auth_permission
(
    id       int auto_increment
        primary key,
    codename varchar(100) not null,
    name     varchar(255) not null,
    constraint codename
        unique (codename)
);

INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (1, 'canManageOwnComments', 'Can manage own comments');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (2, 'canManageAllComments', 'Can manage all comments');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (3, 'canManageOwnNews', 'Can manage own news');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (4, 'canManageAllNews', 'Can manage all news');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (5, 'canManageUsers', 'Can manage users');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (6, 'canManagePermissions', 'Can manage permissions');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (7, 'canManageGroups', 'Can manage groups');
INSERT INTO newsphp_db.auth_permission (id, codename, name) VALUES (8, 'canManageCategories', 'Can manage categories');

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

INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (6, 3, 1);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (13, 4, 1);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (14, 4, 2);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (15, 4, 3);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (37, 1, 2);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (38, 1, 4);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (39, 1, 5);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (40, 1, 6);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (41, 1, 7);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (42, 1, 8);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (43, 2, 2);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (44, 2, 3);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (45, 2, 8);
INSERT INTO newsphp_db.auth_group_permissions (id, group_id, permission_id) VALUES (46, 7, 8);

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

INSERT INTO newsphp_db.auth_user (id, password, username, email) VALUES (19, '$2y$10$3o/QqfJFlJOvQbLzV/ECBuRIWFvouZ/RZeE0DRUZjIK79Z1i6LKBC', 'artem', 'test@gmail.com');
INSERT INTO newsphp_db.auth_user (id, password, username, email) VALUES (23, '$2y$10$eAO6Ae7Px6waXnizbVDc0OU53WMFL.aiZODH0bTrYKmhKj/.nqw2W', 'test123', 'testtest@gmail.com');
INSERT INTO newsphp_db.auth_user (id, password, username, email) VALUES (24, '$2y$10$cWb5WOY2TjICbfWD2KM/3u/LKhnVB5.rv2xfbn5ZH3txescybp/sC', 'Pure', 'pure@php.com');

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

INSERT INTO newsphp_db.auth_user_group (id, user_id, group_id) VALUES (45, 19, 1);
INSERT INTO newsphp_db.auth_user_group (id, user_id, group_id) VALUES (46, 19, 2);
INSERT INTO newsphp_db.auth_user_group (id, user_id, group_id) VALUES (47, 19, 3);
INSERT INTO newsphp_db.auth_user_group (id, user_id, group_id) VALUES (37, 23, 3);
INSERT INTO newsphp_db.auth_user_group (id, user_id, group_id) VALUES (48, 24, 3);

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

INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (4, 'World', 'world', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (5, 'Ukraine', 'ukraine', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (6, 'Tech', 'tech', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (7, 'Business', 'business', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (8, 'Sciense', 'sciense', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (9, 'Health', 'health', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');
INSERT INTO newsphp_db.news_category (id, name, slug, description) VALUES (10, 'Entertainment and Arts', 'entertainment-and-arts', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.');



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

