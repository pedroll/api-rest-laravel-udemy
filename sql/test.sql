create table USERS
(
    id             int auto_increment
        primary key,
    name           varchar(50)  null,
    surname        varchar(50)  null,
    email          varchar(255) null,
    password       varchar(100) null,
    role           varchar(50)  null,
    descripcion    text         null,
    remember_token varchar(255) null,
    created_at     datetime     null,
    updated_at     datetime     null,
    constraint USERS_pk_2
        unique (id)
)
    collate = utf8mb4_spanish_ci;

create table categories
(
    id         int auto_increment
        primary key,
    name       varchar(255) not null,
    created_at datetime     null,
    updated_at datetime     null,
    constraint categories_pk_2
        unique (id)
);

create table posts
(
    id          int auto_increment
        primary key,
    user_id     int          null,
    category_id int          null,
    title       varchar(50)  null,
    content     text         null,
    image       varchar(255) null,
    created_at  datetime     null,
    updated_at  datetime     null,
    constraint categories_pk_2
        unique (id),
    constraint posts_USERS_id_fk
        foreign key (user_id) references USERS (id),
    constraint posts_categories_id_fk
        foreign key (category_id) references categories (id)
);


