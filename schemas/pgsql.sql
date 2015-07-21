--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: chatv2; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA chatv2;


--
-- Name: chatv3; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA chatv3;


--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = chatv3, pg_catalog;

--
-- Name: pjj_boolsum(boolean, boolean); Type: FUNCTION; Schema: chatv3; Owner: -
--

CREATE FUNCTION pjj_boolsum(boolean, boolean) RETURNS boolean
    LANGUAGE sql IMMUTABLE
    AS $_$SELECT
	CASE
	WHEN ($1 IS NULL) THEN $2
	WHEN ($2 IS NULL) THEN $1
	ELSE ($1 OR $2)::bool
	END
;$_$;


--
-- Name: pjj_ec_like(text); Type: FUNCTION; Schema: chatv3; Owner: -
--

CREATE FUNCTION pjj_ec_like(text) RETURNS text
    LANGUAGE sql IMMUTABLE STRICT
    AS $_$SELECT REPLACE(REPLACE($1, '%', '\\%'), '_', '\\_');$_$;


--
-- Name: pjj_boolaggr(boolean); Type: AGGREGATE; Schema: chatv3; Owner: -
--

CREATE AGGREGATE pjj_boolaggr(boolean) (
    SFUNC = pjj_boolsum,
    STYPE = boolean
);


--
-- Name: pjj_concat(text); Type: AGGREGATE; Schema: chatv3; Owner: -
--

CREATE AGGREGATE pjj_concat(text) (
    SFUNC = textcat,
    STYPE = text,
    FINALFUNC = pg_catalog.rtrim
);


SET search_path = chatv2, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: adminlog; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE adminlog (
    entry_id integer NOT NULL,
    chat_id integer,
    page_id integer,
    user_id integer,
    stamp timestamp with time zone,
    user_ip inet
);


--
-- Name: TABLE adminlog; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE adminlog IS 'uo_chat_adminlog';


--
-- Name: adminlog_entry_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE adminlog_entry_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: adminlog_entry_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE adminlog_entry_id_seq OWNED BY adminlog.entry_id;


--
-- Name: applications; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE applications (
    id integer NOT NULL,
    chat text,
    username text,
    email text,
    faction integer,
    description text,
    rtime timestamp with time zone,
    appstat integer
);
ALTER TABLE ONLY applications ALTER COLUMN chat SET STORAGE MAIN;
ALTER TABLE ONLY applications ALTER COLUMN username SET STORAGE MAIN;
ALTER TABLE ONLY applications ALTER COLUMN email SET STORAGE MAIN;


--
-- Name: TABLE applications; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE applications IS 'uo_chat_regapps';


--
-- Name: applications_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE applications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: applications_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE applications_id_seq OWNED BY applications.id;


--
-- Name: bans; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE bans (
    chat text,
    ident text,
    utime timestamp with time zone,
    auth text
);
ALTER TABLE ONLY bans ALTER COLUMN chat SET STORAGE MAIN;
ALTER TABLE ONLY bans ALTER COLUMN ident SET STORAGE MAIN;
ALTER TABLE ONLY bans ALTER COLUMN auth SET STORAGE MAIN;


--
-- Name: TABLE bans; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE bans IS 'uo_chat_ban';


--
-- Name: chats; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE chats (
    chat text NOT NULL,
    email text,
    owner text,
    numwarn integer,
    ctime timestamp with time zone,
    utime timestamp with time zone,
    prefs text,
    regnotes text,
    chat_id integer NOT NULL,
    savedpath text,
    dtime timestamp with time zone
);
ALTER TABLE ONLY chats ALTER COLUMN chat SET STORAGE MAIN;
ALTER TABLE ONLY chats ALTER COLUMN email SET STORAGE MAIN;
ALTER TABLE ONLY chats ALTER COLUMN owner SET STORAGE MAIN;
ALTER TABLE ONLY chats ALTER COLUMN prefs SET STORAGE MAIN;
ALTER TABLE ONLY chats ALTER COLUMN savedpath SET STORAGE MAIN;


--
-- Name: TABLE chats; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE chats IS 'uo_chat_last';


--
-- Name: chats_chat_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE chats_chat_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: chats_chat_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE chats_chat_id_seq OWNED BY chats.chat_id;


--
-- Name: factions; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE factions (
    chat text,
    id integer NOT NULL,
    name text,
    icon text
);


--
-- Name: TABLE factions; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE factions IS 'uo_chat_faction';


--
-- Name: factions_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE factions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: factions_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE factions_id_seq OWNED BY factions.id;


--
-- Name: ignores; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE ignores (
    chat text,
    ident text,
    utime timestamp with time zone,
    auth text
);


--
-- Name: TABLE ignores; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE ignores IS 'uo_chat_ignore';


--
-- Name: image_cache; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE image_cache (
    width integer NOT NULL,
    height integer NOT NULL,
    url text NOT NULL,
    sum text NOT NULL
);
ALTER TABLE ONLY image_cache ALTER COLUMN url SET STORAGE MAIN;
ALTER TABLE ONLY image_cache ALTER COLUMN sum SET STORAGE MAIN;


--
-- Name: TABLE image_cache; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE image_cache IS 'uo_chat_images';


--
-- Name: index_words; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE index_words (
    word_id integer NOT NULL,
    word_text text NOT NULL
);


--
-- Name: index_words_word_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE index_words_word_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: index_words_word_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE index_words_word_id_seq OWNED BY index_words.word_id;


--
-- Name: log_immediate; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE log_immediate (
    chat text,
    id integer,
    ident text,
    line text,
    username text,
    ip inet,
    csig text,
    proxyip inet,
    posttime timestamp with time zone,
    rawpost text,
    xmlpost text,
    color text
);


--
-- Name: TABLE log_immediate; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE log_immediate IS 'uo_chat_log';


--
-- Name: messages; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE messages (
    message_id integer NOT NULL,
    chat text,
    username text,
    msg text,
    auth text,
    utime timestamp with time zone,
    deleted boolean,
    unread boolean
);


--
-- Name: TABLE messages; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE messages IS 'uo_chat_messages';


--
-- Name: messages_message_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE messages_message_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: messages_message_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE messages_message_id_seq OWNED BY messages.message_id;


--
-- Name: polls; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE polls (
    chat text NOT NULL,
    topic text,
    nselect integer,
    ta text,
    ca text,
    tb text,
    cb text,
    tc text,
    cc text,
    td text,
    cd text,
    te text,
    ce text
);


--
-- Name: TABLE polls; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE polls IS 'uo_chat_poll';


--
-- Name: posts; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE posts (
    chat text,
    id integer NOT NULL,
    utime timestamp with time zone,
    topic text,
    post text,
    username text
);


--
-- Name: TABLE posts; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE posts IS 'uo_chat_threads';


--
-- Name: posts_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE posts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: posts_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE posts_id_seq OWNED BY posts.id;


--
-- Name: seen_urls; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE seen_urls (
    url_id integer NOT NULL,
    url_poster text,
    url_poster_id integer,
    url_time timestamp with time zone,
    url_href text,
    url_chat integer
);
ALTER TABLE ONLY seen_urls ALTER COLUMN url_poster SET STORAGE MAIN;
ALTER TABLE ONLY seen_urls ALTER COLUMN url_href SET STORAGE MAIN;


--
-- Name: seen_urls_url_id_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE seen_urls_url_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: seen_urls_url_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE seen_urls_url_id_seq OWNED BY seen_urls.url_id;


--
-- Name: threads; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE threads (
    chat text,
    flags text,
    utime timestamp with time zone,
    topic text,
    username text,
    ctime timestamp with time zone,
    hits integer,
    uid integer NOT NULL
);


--
-- Name: TABLE threads; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE threads IS 'uo_chat_board';


--
-- Name: threads_uid_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE threads_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: threads_uid_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE threads_uid_seq OWNED BY threads.uid;


--
-- Name: userlist; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE userlist (
    chat text,
    ident text,
    username text,
    link text,
    image text,
    utime timestamp with time zone
);


--
-- Name: TABLE userlist; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE userlist IS 'uo_chat_ulist';


--
-- Name: users; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE users (
    chat text,
    username text,
    password text,
    flags text,
    email text,
    faction integer,
    prefs text,
    icq integer,
    aim text,
    ym text,
    msn text,
    site text,
    uid integer NOT NULL,
    profile text,
    icon text,
    chain text,
    picon text,
    pimage text,
    plink text,
    pcolor text,
    lastlogin timestamp with time zone,
    skype text
);


--
-- Name: TABLE users; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE users IS 'uo_chat_database';


--
-- Name: users_uid_seq; Type: SEQUENCE; Schema: chatv2; Owner: -
--

CREATE SEQUENCE users_uid_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_uid_seq; Type: SEQUENCE OWNED BY; Schema: chatv2; Owner: -
--

ALTER SEQUENCE users_uid_seq OWNED BY users.uid;


--
-- Name: viewers; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE viewers (
    utime timestamp without time zone,
    ip inet,
    chat text,
    proxyip inet,
    user_agent text
);


--
-- Name: TABLE viewers; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE viewers IS 'uo_chat';


--
-- Name: votes; Type: TABLE; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE TABLE votes (
    chat text,
    utime timestamp with time zone,
    username text,
    password text,
    email text,
    ip inet,
    vote integer,
    valid boolean
);


--
-- Name: TABLE votes; Type: COMMENT; Schema: chatv2; Owner: -
--

COMMENT ON TABLE votes IS 'uo_chat_vote';


SET search_path = chatv3, pg_catalog;

--
-- Name: pjj_application_forms; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_application_forms (
    id integer NOT NULL,
    chat integer,
    name text NOT NULL,
    fields text,
    creator integer
);


--
-- Name: pjj_application_forms_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_application_forms_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_application_forms_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_application_forms_id_seq OWNED BY pjj_application_forms.id;


--
-- Name: pjj_ban; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_ban (
    chat integer,
    ident character varying(8),
    auth integer,
    uid integer,
    hmask text,
    id integer NOT NULL,
    authid character varying(32),
    expires timestamp with time zone DEFAULT (now() + '01:00:00'::interval),
    perm boolean DEFAULT false NOT NULL,
    ipmask inet,
    type integer DEFAULT 0 NOT NULL,
    hostmask text
);


--
-- Name: COLUMN pjj_ban.hmask; Type: COMMENT; Schema: chatv3; Owner: -
--

COMMENT ON COLUMN pjj_ban.hmask IS 'Handle mask';


--
-- Name: pjj_ban_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_ban_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_ban_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_ban_id_seq OWNED BY pjj_ban.id;


--
-- Name: pjj_categories; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_categories (
    id smallint DEFAULT nextval(('chatv3.pjj_categories_id_seq'::text)::regclass) NOT NULL,
    name text,
    description text,
    shortdesc text,
    ordering smallint
);


--
-- Name: pjj_categories_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_channels; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_channels (
    chat integer NOT NULL,
    name text NOT NULL,
    read_access text,
    id integer DEFAULT nextval(('chatv3.pjj_channels_id_seq'::text)::regclass) NOT NULL,
    write_access text
);


--
-- Name: pjj_channels_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_channels_id_seq
    START WITH 3
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


SET default_with_oids = true;

--
-- Name: pjj_chats; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_chats (
    id integer DEFAULT nextval(('chatv3.pjj_chats_id_seq'::text)::regclass) NOT NULL,
    path text,
    created timestamp without time zone DEFAULT now(),
    flags text,
    prefs text,
    title text,
    description text,
    category smallint DEFAULT 1,
    timeout integer,
    lastmsg timestamp with time zone DEFAULT now(),
    parent integer,
    form_lastmsg text,
    form_timezone character varying(8),
    form_lastpost text,
    s_chatlines integer,
    s_identlength integer,
    s_defaulthandle text,
    s_maxhandle integer,
    theme integer,
    read_access text,
    write_access text,
    s_postlines integer,
    s_postlength integer,
    s_welcome_page text,
    s_motd text,
    s_css text,
    s_logo text,
    s_logolink text
);


--
-- Name: pjj_chats_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_chats_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


SET default_with_oids = false;

--
-- Name: pjj_chatsettings; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_chatsettings (
    chat integer NOT NULL,
    set_name text NOT NULL,
    set_value text
);


--
-- Name: pjj_cookies; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_cookies (
    id character varying(32) NOT NULL,
    data text,
    stamp timestamp without time zone DEFAULT now()
);


--
-- Name: pjj_country_ips; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_country_ips (
    range_start inet NOT NULL,
    range_end inet NOT NULL,
    country integer NOT NULL
);


--
-- Name: pjj_country_names; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_country_names (
    id integer NOT NULL,
    name text NOT NULL,
    name2 character(2) NOT NULL,
    name3 character(3) NOT NULL
);


--
-- Name: pjj_factionassoc; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_factionassoc (
    id integer,
    userid integer,
    flags text
);


--
-- Name: pjj_factions; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_factions (
    chat integer,
    id integer NOT NULL,
    name text,
    profile text,
    symbol integer,
    CONSTRAINT factions_chat CHECK ((chat > 0))
);


--
-- Name: pjj_factions_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_factions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_factions_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_factions_id_seq OWNED BY pjj_factions.id;


--
-- Name: pjj_userassoc; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userassoc (
    chat integer NOT NULL,
    id integer NOT NULL,
    flags text,
    lastvisit timestamp with time zone DEFAULT now(),
    visual_image text,
    visual_link text,
    visual_color text,
    visual_symbol integer,
    visual_icon text,
    visual_status integer
);


SET default_with_oids = true;

--
-- Name: pjj_users; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_users (
    id integer DEFAULT nextval(('chatv3.pjj_users_id_seq'::text)::regclass) NOT NULL,
    chat integer DEFAULT 0 NOT NULL,
    parent integer,
    username text NOT NULL,
    email text NOT NULL,
    icq integer,
    ym text,
    aim text,
    msn text,
    jabber text,
    site text,
    prefs text,
    profile text,
    verified character varying(32) NOT NULL,
    style character varying(32),
    lastlogin timestamp with time zone DEFAULT now(),
    created timestamp with time zone DEFAULT now() NOT NULL,
    timezone character varying(8),
    secur_hostmask text,
    secur_sessionid character varying(32),
    secur_slevel integer,
    country character(2),
    password text,
    secur_ipmask inet,
    skype text,
    CONSTRAINT icq CHECK ((icq >= 0)),
    CONSTRAINT security_session_level CHECK (((secur_slevel >= 1) AND (secur_slevel <= 4)))
);


--
-- Name: pjj_flagsforchat; Type: VIEW; Schema: chatv3; Owner: -
--

CREATE VIEW pjj_flagsforchat AS
 SELECT users.id AS uid,
    users.parent,
    mchats.id AS chat,
    pjj_concat(assoc.flags) AS flags
   FROM (((pjj_chats mchats
     JOIN pjj_chats chats ON ((((chats.id > 0) AND (mchats.id > 0)) AND ((chats.id = mchats.parent) OR (chats.id = mchats.id)))))
     JOIN pjj_userassoc assoc ON (((((chats.id = assoc.chat) OR (assoc.chat = 0)) AND (assoc.flags <> '1'::text)) AND (assoc.flags IS NOT NULL))))
     JOIN pjj_users users ON (((assoc.id = users.id) OR (assoc.id = users.parent))))
  GROUP BY users.id, users.parent, mchats.id;


SET default_with_oids = false;

--
-- Name: pjj_icons; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_icons (
    id integer NOT NULL,
    suficon text,
    chat integer DEFAULT 0,
    userid integer,
    pub boolean,
    faction integer,
    name text,
    preicon text
);


--
-- Name: pjj_icons_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_icons_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_icons_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_icons_id_seq OWNED BY pjj_icons.id;


--
-- Name: pjj_iconsexpanded; Type: VIEW; Schema: chatv3; Owner: -
--

CREATE VIEW pjj_iconsexpanded AS
 SELECT icons.id,
    icons.chat,
    icons.preicon,
    icons.suficon,
    icons.userid,
    icons.faction,
    icons.name,
    icons.pub,
    users.username,
    facts.name AS factionname
   FROM ((pjj_icons icons
     LEFT JOIN pjj_factions facts ON ((icons.faction = facts.id)))
     LEFT JOIN pjj_users users ON ((icons.userid = users.id)));


--
-- Name: pjj_image_meta; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_image_meta (
    meta_id integer NOT NULL,
    image_id integer NOT NULL,
    user_id integer,
    chat_id integer,
    faction_id integer,
    name text,
    description text,
    flags text
);


--
-- Name: pjj_image_meta_meta_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_image_meta_meta_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_image_meta_meta_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_image_meta_meta_id_seq OWNED BY pjj_image_meta.meta_id;


--
-- Name: pjj_imageban; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_imageban (
    id integer NOT NULL,
    chat integer DEFAULT 0 NOT NULL,
    banmask text,
    userid integer
);


--
-- Name: pjj_imageban_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_imageban_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_imageban_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_imageban_id_seq OWNED BY pjj_imageban.id;


--
-- Name: pjj_imagecache; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_imagecache (
    width integer,
    height integer,
    sum integer NOT NULL,
    stamp timestamp without time zone DEFAULT now()
);


--
-- Name: pjj_images; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_images (
    id integer NOT NULL,
    hash text,
    created timestamp with time zone DEFAULT now(),
    lasthit timestamp with time zone DEFAULT now(),
    size integer,
    width integer,
    height integer,
    mime_id integer,
    banned boolean
);


--
-- Name: pjj_images_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_images_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_images_id_seq OWNED BY pjj_images.id;


--
-- Name: pjj_log; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_log (
    chat integer NOT NULL,
    ident character varying(8),
    line text,
    username text,
    userid integer,
    stamp timestamp without time zone DEFAULT now() NOT NULL,
    ip inet,
    channel integer DEFAULT 0 NOT NULL,
    id integer DEFAULT 0 NOT NULL,
    flags text,
    browserid text
);


--
-- Name: pjj_log_big; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_log_big (
    chat integer NOT NULL,
    ident character varying(8),
    line text,
    username text,
    userid integer,
    stamp timestamp without time zone DEFAULT now(),
    id integer NOT NULL,
    ip inet,
    channel integer DEFAULT 0 NOT NULL,
    browserid text
);


--
-- Name: pjj_log_big_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_log_big_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_log_big_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_log_big_id_seq OWNED BY pjj_log_big.id;


--
-- Name: pjj_messages; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_messages (
    id integer NOT NULL,
    recipient integer,
    realrecp integer,
    author integer,
    topic character varying,
    post text,
    status character varying(10) DEFAULT 'unread'::character varying,
    posttime timestamp with time zone DEFAULT now()
);


--
-- Name: pjj_messages_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_messages_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_messages_id_seq OWNED BY pjj_messages.id;


--
-- Name: pjj_mime_types; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_mime_types (
    mime_id integer NOT NULL,
    mime_type text NOT NULL
);


--
-- Name: pjj_mime_types_mime_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_mime_types_mime_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_mime_types_mime_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_mime_types_mime_id_seq OWNED BY pjj_mime_types.mime_id;


--
-- Name: pjj_online; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_online (
    chat integer NOT NULL,
    session_id integer NOT NULL,
    ips inet NOT NULL,
    uid integer,
    utime timestamp with time zone DEFAULT now()
);


--
-- Name: pjj_ownedchats; Type: VIEW; Schema: chatv3; Owner: -
--

CREATE VIEW pjj_ownedchats AS
 SELECT DISTINCT ON (users.id, mchats.id) users.id AS uid,
    users.parent,
    mchats.id,
    mchats.title,
    assoc.flags
   FROM (((pjj_chats mchats
     JOIN pjj_chats chats ON ((((chats.id > 0) AND (mchats.id > 0)) AND ((chats.id = mchats.parent) OR (chats.id = mchats.id)))))
     JOIN pjj_userassoc assoc ON (((((chats.id = assoc.chat) OR (assoc.chat = 0)) AND (assoc.flags IS NOT NULL)) AND (assoc.flags ~~ '%M%'::text))))
     JOIN pjj_users users ON (((users.chat = 0) AND ((assoc.id = users.id) OR (assoc.id = users.parent)))))
  ORDER BY users.id, mchats.id;


--
-- Name: pjj_replaces; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_replaces (
    chat integer DEFAULT 0 NOT NULL,
    id integer NOT NULL,
    afind text NOT NULL,
    arepl text,
    preg boolean DEFAULT false NOT NULL,
    raw boolean DEFAULT false NOT NULL
);


--
-- Name: pjj_replaces_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_replaces_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_replaces_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_replaces_id_seq OWNED BY pjj_replaces.id;


--
-- Name: pjj_sessions; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_sessions (
    id character varying(32),
    location text,
    stamp timestamp without time zone DEFAULT now(),
    ip inet
);


--
-- Name: pjj_symbols; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_symbols (
    id integer NOT NULL,
    chat integer,
    prefix text,
    suffix text,
    flags text,
    name text
);


--
-- Name: pjj_symbols_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_symbols_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_symbols_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_symbols_id_seq OWNED BY pjj_symbols.id;


--
-- Name: pjj_themes; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_themes (
    id integer NOT NULL,
    name text,
    description text,
    chat integer,
    creator integer
);


--
-- Name: pjj_themes_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_themes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_themes_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_themes_id_seq OWNED BY pjj_themes.id;


--
-- Name: pjj_user_flags; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_user_flags (
    user_id integer NOT NULL,
    chat_id integer NOT NULL,
    flag_owner boolean,
    flag_master boolean,
    flag_clear boolean,
    flag_moderator boolean,
    flag_ban boolean,
    flag_stealth boolean,
    flag_faction_manager boolean,
    flag_member boolean
);


--
-- Name: pjj_userbook; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userbook (
    userid integer NOT NULL,
    chat integer NOT NULL,
    rating smallint,
    CONSTRAINT rating CHECK (((rating >= (-5)) AND (rating <= 5)))
);


--
-- Name: pjj_userfriends; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userfriends (
    userid integer NOT NULL,
    friend integer NOT NULL,
    rating integer NOT NULL,
    CONSTRAINT rating_userfriends CHECK (((rating >= (-5)) AND (rating <= 5)))
);


--
-- Name: pjj_userlist; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userlist (
    chat integer,
    username character varying,
    link text,
    image text,
    ident character varying(8),
    userid integer,
    posttime timestamp with time zone DEFAULT now()
);


--
-- Name: pjj_userrecent; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userrecent (
    userid integer NOT NULL,
    chat integer NOT NULL,
    stamp timestamp without time zone DEFAULT now()
);


--
-- Name: pjj_users_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_userstates; Type: TABLE; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE TABLE pjj_userstates (
    id integer NOT NULL,
    chat integer DEFAULT 0 NOT NULL,
    prefix text,
    suffix text,
    name text NOT NULL
);


--
-- Name: pjj_userstates_id_seq; Type: SEQUENCE; Schema: chatv3; Owner: -
--

CREATE SEQUENCE pjj_userstates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pjj_userstates_id_seq; Type: SEQUENCE OWNED BY; Schema: chatv3; Owner: -
--

ALTER SEQUENCE pjj_userstates_id_seq OWNED BY pjj_userstates.id;


SET search_path = chatv2, pg_catalog;

--
-- Name: entry_id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY adminlog ALTER COLUMN entry_id SET DEFAULT nextval('adminlog_entry_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY applications ALTER COLUMN id SET DEFAULT nextval('applications_id_seq'::regclass);


--
-- Name: chat_id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY chats ALTER COLUMN chat_id SET DEFAULT nextval('chats_chat_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY factions ALTER COLUMN id SET DEFAULT nextval('factions_id_seq'::regclass);


--
-- Name: word_id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY index_words ALTER COLUMN word_id SET DEFAULT nextval('index_words_word_id_seq'::regclass);


--
-- Name: message_id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY messages ALTER COLUMN message_id SET DEFAULT nextval('messages_message_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY posts ALTER COLUMN id SET DEFAULT nextval('posts_id_seq'::regclass);


--
-- Name: url_id; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY seen_urls ALTER COLUMN url_id SET DEFAULT nextval('seen_urls_url_id_seq'::regclass);


--
-- Name: uid; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY threads ALTER COLUMN uid SET DEFAULT nextval('threads_uid_seq'::regclass);


--
-- Name: uid; Type: DEFAULT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY users ALTER COLUMN uid SET DEFAULT nextval('users_uid_seq'::regclass);


SET search_path = chatv3, pg_catalog;

--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_application_forms ALTER COLUMN id SET DEFAULT nextval('pjj_application_forms_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_ban ALTER COLUMN id SET DEFAULT nextval('pjj_ban_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_factions ALTER COLUMN id SET DEFAULT nextval('pjj_factions_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_icons ALTER COLUMN id SET DEFAULT nextval('pjj_icons_id_seq'::regclass);


--
-- Name: meta_id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_image_meta ALTER COLUMN meta_id SET DEFAULT nextval('pjj_image_meta_meta_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_imageban ALTER COLUMN id SET DEFAULT nextval('pjj_imageban_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_images ALTER COLUMN id SET DEFAULT nextval('pjj_images_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_log_big ALTER COLUMN id SET DEFAULT nextval('pjj_log_big_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_messages ALTER COLUMN id SET DEFAULT nextval('pjj_messages_id_seq'::regclass);


--
-- Name: mime_id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_mime_types ALTER COLUMN mime_id SET DEFAULT nextval('pjj_mime_types_mime_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_replaces ALTER COLUMN id SET DEFAULT nextval('pjj_replaces_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_symbols ALTER COLUMN id SET DEFAULT nextval('pjj_symbols_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_themes ALTER COLUMN id SET DEFAULT nextval('pjj_themes_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userstates ALTER COLUMN id SET DEFAULT nextval('pjj_userstates_id_seq'::regclass);


SET search_path = chatv2, pg_catalog;

--
-- Name: adminlog_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY adminlog
    ADD CONSTRAINT adminlog_pkey PRIMARY KEY (entry_id);


--
-- Name: applications_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY applications
    ADD CONSTRAINT applications_pkey PRIMARY KEY (id);


--
-- Name: chats_chat_key; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY chats
    ADD CONSTRAINT chats_chat_key UNIQUE (chat);


--
-- Name: chats_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY chats
    ADD CONSTRAINT chats_pkey PRIMARY KEY (chat_id);


--
-- Name: factions_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY factions
    ADD CONSTRAINT factions_pkey PRIMARY KEY (id);


--
-- Name: image_cache_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY image_cache
    ADD CONSTRAINT image_cache_pkey PRIMARY KEY (sum);


--
-- Name: index_words_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY index_words
    ADD CONSTRAINT index_words_pkey PRIMARY KEY (word_id);


--
-- Name: index_words_word_text_key; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY index_words
    ADD CONSTRAINT index_words_word_text_key UNIQUE (word_text);


--
-- Name: messages_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (message_id);


--
-- Name: polls_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY polls
    ADD CONSTRAINT polls_pkey PRIMARY KEY (chat);


--
-- Name: posts_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY posts
    ADD CONSTRAINT posts_pkey PRIMARY KEY (id);


--
-- Name: seen_urls_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY seen_urls
    ADD CONSTRAINT seen_urls_pkey PRIMARY KEY (url_id);


--
-- Name: threads_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY threads
    ADD CONSTRAINT threads_pkey PRIMARY KEY (uid);


--
-- Name: users_pkey; Type: CONSTRAINT; Schema: chatv2; Owner: -; Tablespace: 
--

ALTER TABLE ONLY users
    ADD CONSTRAINT users_pkey PRIMARY KEY (uid);


SET search_path = chatv3, pg_catalog;

--
-- Name: idi; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_images
    ADD CONSTRAINT idi PRIMARY KEY (id);


--
-- Name: idx; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_themes
    ADD CONSTRAINT idx PRIMARY KEY (id);


--
-- Name: iid; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_icons
    ADD CONSTRAINT iid PRIMARY KEY (id);


--
-- Name: pjj_appplication_forms_chat_key; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_application_forms
    ADD CONSTRAINT pjj_appplication_forms_chat_key UNIQUE (chat, name);


--
-- Name: pjj_appplication_forms_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_application_forms
    ADD CONSTRAINT pjj_appplication_forms_pkey PRIMARY KEY (id);


--
-- Name: pjj_ban_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_ban
    ADD CONSTRAINT pjj_ban_pkey PRIMARY KEY (id);


--
-- Name: pjj_categories_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_categories
    ADD CONSTRAINT pjj_categories_pkey PRIMARY KEY (id);


--
-- Name: pjj_channels_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_channels
    ADD CONSTRAINT pjj_channels_pkey PRIMARY KEY (id);


--
-- Name: pjj_chats_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_chats
    ADD CONSTRAINT pjj_chats_pkey PRIMARY KEY (id);


--
-- Name: pjj_chatsettings_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_chatsettings
    ADD CONSTRAINT pjj_chatsettings_pkey PRIMARY KEY (chat, set_name);


--
-- Name: pjj_cookies_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_cookies
    ADD CONSTRAINT pjj_cookies_pkey PRIMARY KEY (id);


--
-- Name: pjj_country_ips_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_country_ips
    ADD CONSTRAINT pjj_country_ips_pkey PRIMARY KEY (range_start);


--
-- Name: pjj_country_names_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_country_names
    ADD CONSTRAINT pjj_country_names_pkey PRIMARY KEY (id);


--
-- Name: pjj_factions_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_factions
    ADD CONSTRAINT pjj_factions_pkey PRIMARY KEY (id);


--
-- Name: pjj_image_meta_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_image_meta
    ADD CONSTRAINT pjj_image_meta_pkey PRIMARY KEY (meta_id);


--
-- Name: pjj_imageban_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_imageban
    ADD CONSTRAINT pjj_imageban_pkey PRIMARY KEY (id);


--
-- Name: pjj_imagecache_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_imagecache
    ADD CONSTRAINT pjj_imagecache_pkey PRIMARY KEY (sum);


--
-- Name: pjj_images_hash_key; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_images
    ADD CONSTRAINT pjj_images_hash_key UNIQUE (hash);


--
-- Name: pjj_log_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_log
    ADD CONSTRAINT pjj_log_pkey PRIMARY KEY (chat, channel, stamp);


--
-- Name: pjj_messages_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_messages
    ADD CONSTRAINT pjj_messages_pkey PRIMARY KEY (id);


--
-- Name: pjj_mime_types_mime_type_key; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_mime_types
    ADD CONSTRAINT pjj_mime_types_mime_type_key UNIQUE (mime_type);


--
-- Name: pjj_mime_types_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_mime_types
    ADD CONSTRAINT pjj_mime_types_pkey PRIMARY KEY (mime_id);


--
-- Name: pjj_online_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_online
    ADD CONSTRAINT pjj_online_pkey PRIMARY KEY (chat, ips);


--
-- Name: pjj_replaces_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_replaces
    ADD CONSTRAINT pjj_replaces_pkey PRIMARY KEY (id);


--
-- Name: pjj_symbols_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_symbols
    ADD CONSTRAINT pjj_symbols_pkey PRIMARY KEY (id);


--
-- Name: pjj_user_flags_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_user_flags
    ADD CONSTRAINT pjj_user_flags_pkey PRIMARY KEY (chat_id, user_id);


--
-- Name: pjj_userassoc_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_userassoc
    ADD CONSTRAINT pjj_userassoc_pkey PRIMARY KEY (chat, id);


--
-- Name: pjj_userrecent_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_userrecent
    ADD CONSTRAINT pjj_userrecent_pkey PRIMARY KEY (userid, chat);


--
-- Name: pjj_users_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_users
    ADD CONSTRAINT pjj_users_pkey PRIMARY KEY (id);


--
-- Name: pjj_userstates_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_userstates
    ADD CONSTRAINT pjj_userstates_pkey PRIMARY KEY (id);


--
-- Name: pkey_userbook; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_userbook
    ADD CONSTRAINT pkey_userbook PRIMARY KEY (userid, chat);


--
-- Name: userfriends_pkey; Type: CONSTRAINT; Schema: chatv3; Owner: -; Tablespace: 
--

ALTER TABLE ONLY pjj_userfriends
    ADD CONSTRAINT userfriends_pkey PRIMARY KEY (userid, friend);


SET search_path = chatv2, pg_catalog;

--
-- Name: fki_seen_urls_chat; Type: INDEX; Schema: chatv2; Owner: -; Tablespace: 
--

CREATE INDEX fki_seen_urls_chat ON seen_urls USING btree (url_chat);


SET search_path = chatv3, pg_catalog;

--
-- Name: author; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX author ON pjj_messages USING btree (author);


--
-- Name: category_pjj_chats_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX category_pjj_chats_index ON pjj_chats USING btree (category);


--
-- Name: chat_ban; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_ban ON pjj_ban USING btree (chat);


--
-- Name: chat_channels; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_channels ON pjj_channels USING btree (chat);


--
-- Name: chat_factions; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_factions ON pjj_factions USING btree (chat);


--
-- Name: chat_icons; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_icons ON pjj_icons USING btree (chat);


--
-- Name: chat_log; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_log ON pjj_log USING btree (chat);


--
-- Name: chat_log_big; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_log_big ON pjj_log_big USING btree (chat);


--
-- Name: chat_online; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_online ON pjj_online USING btree (chat);


--
-- Name: chat_pjj_users_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_pjj_users_index ON pjj_users USING btree (chat);


--
-- Name: chat_themes; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_themes ON pjj_themes USING btree (chat);


--
-- Name: chat_userassoc; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_userassoc ON pjj_userassoc USING btree (chat);


--
-- Name: chat_userbook; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_userbook ON pjj_userbook USING btree (chat);


--
-- Name: chat_userlist; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_userlist ON pjj_userlist USING btree (chat);


--
-- Name: chat_userrecent; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX chat_userrecent ON pjj_userrecent USING btree (chat);


--
-- Name: country_id; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX country_id ON pjj_country_names USING btree (id);


--
-- Name: created_images; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX created_images ON pjj_images USING btree (created);


--
-- Name: creator_themes; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX creator_themes ON pjj_themes USING btree (creator);


--
-- Name: fki_; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX fki_ ON pjj_images USING btree (mime_id);


--
-- Name: fki_auth; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX fki_auth ON pjj_ban USING btree (auth);


--
-- Name: fki_creator; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX fki_creator ON pjj_application_forms USING btree (creator);


--
-- Name: friend_userfriends; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX friend_userfriends ON pjj_userfriends USING btree (friend);


--
-- Name: height_images; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX height_images ON pjj_images USING btree (height);


--
-- Name: id_ban; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_ban ON pjj_ban USING btree (id);


--
-- Name: id_categories; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_categories ON pjj_categories USING btree (id);


--
-- Name: id_chats; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_chats ON pjj_chats USING btree (id);


--
-- Name: id_cookies; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_cookies ON pjj_cookies USING btree (id);


--
-- Name: id_factionassoc; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX id_factionassoc ON pjj_factionassoc USING btree (id);


--
-- Name: id_factions; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_factions ON pjj_factions USING btree (id);


--
-- Name: id_icons; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_icons ON pjj_icons USING btree (id);


--
-- Name: id_log_big; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX id_log_big ON pjj_log_big USING btree (id);


--
-- Name: id_messages; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_messages ON pjj_messages USING btree (id);


--
-- Name: id_sessions; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX id_sessions ON pjj_sessions USING btree (id);


--
-- Name: id_themes; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_themes ON pjj_themes USING btree (id);


--
-- Name: id_userassoc; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX id_userassoc ON pjj_userassoc USING btree (id);


--
-- Name: id_users; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX id_users ON pjj_users USING btree (id);


--
-- Name: ips_country; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX ips_country ON pjj_country_ips USING btree (country);


--
-- Name: ips_end; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX ips_end ON pjj_country_ips USING btree (range_end);


--
-- Name: ips_start; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX ips_start ON pjj_country_ips USING btree (range_start);


--
-- Name: log_big_channel; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX log_big_channel ON pjj_log_big USING btree (channel);


--
-- Name: log_channel; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX log_channel ON pjj_log USING btree (channel);


--
-- Name: meta_image_id_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX meta_image_id_index ON pjj_image_meta USING btree (image_id);


--
-- Name: meta_name_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX meta_name_index ON pjj_image_meta USING btree (name);


--
-- Name: names_name2; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX names_name2 ON pjj_country_names USING btree (name2);


--
-- Name: order_categories; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX order_categories ON pjj_categories USING btree (ordering);


--
-- Name: parent_chats; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX parent_chats ON pjj_chats USING btree (parent);


--
-- Name: parent_pjj_users_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX parent_pjj_users_index ON pjj_users USING btree (parent);


--
-- Name: path_pjj_chats_index; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX path_pjj_chats_index ON pjj_chats USING btree (path);


--
-- Name: posttime_userlist; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX posttime_userlist ON pjj_userlist USING btree (posttime);


--
-- Name: rating_userfriends; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX rating_userfriends ON pjj_userfriends USING btree (rating);


--
-- Name: realrecp_messages; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX realrecp_messages ON pjj_messages USING btree (realrecp);


--
-- Name: replace_chat; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX replace_chat ON pjj_replaces USING btree (chat);


--
-- Name: replace_preg; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX replace_preg ON pjj_replaces USING btree (preg);


--
-- Name: size_images; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX size_images ON pjj_images USING btree (size);


--
-- Name: status_messages; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX status_messages ON pjj_messages USING btree (status);


--
-- Name: sum_imagecache; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX sum_imagecache ON pjj_imagecache USING btree (sum);


--
-- Name: symbold_id; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX symbold_id ON pjj_symbols USING btree (id);


--
-- Name: symbols_chat; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX symbols_chat ON pjj_symbols USING btree (chat);


--
-- Name: type_ban; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX type_ban ON pjj_ban USING btree (type);


--
-- Name: uid; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX uid ON pjj_online USING btree (uid);


--
-- Name: uid_ban; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX uid_ban ON pjj_ban USING btree (uid);


--
-- Name: user_flags_chat_id; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX user_flags_chat_id ON pjj_user_flags USING btree (chat_id);


--
-- Name: user_flags_user_id; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX user_flags_user_id ON pjj_user_flags USING btree (user_id);


--
-- Name: userid_factionassoc; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX userid_factionassoc ON pjj_factionassoc USING btree (userid);


--
-- Name: userid_icons; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX userid_icons ON pjj_icons USING btree (userid);


--
-- Name: userid_userbook; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX userid_userbook ON pjj_userbook USING btree (userid);


--
-- Name: userid_userfriends; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX userid_userfriends ON pjj_userfriends USING btree (userid);


--
-- Name: userid_userlist; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX userid_userlist ON pjj_userlist USING btree (userid);


--
-- Name: users_country; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX users_country ON pjj_users USING btree (country);


--
-- Name: users_unique; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE UNIQUE INDEX users_unique ON pjj_users USING btree (chat, username);


--
-- Name: width_images; Type: INDEX; Schema: chatv3; Owner: -; Tablespace: 
--

CREATE INDEX width_images ON pjj_images USING btree (width);


SET search_path = chatv2, pg_catalog;

--
-- Name: fk_seen_urls_chat; Type: FK CONSTRAINT; Schema: chatv2; Owner: -
--

ALTER TABLE ONLY seen_urls
    ADD CONSTRAINT fk_seen_urls_chat FOREIGN KEY (url_chat) REFERENCES chats(chat_id) ON UPDATE CASCADE ON DELETE CASCADE;


SET search_path = chatv3, pg_catalog;

--
-- Name: $1; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_user_flags
    ADD CONSTRAINT "$1" FOREIGN KEY (user_id) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_images
    ADD CONSTRAINT "$1" FOREIGN KEY (mime_id) REFERENCES pjj_mime_types(mime_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: $1; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_image_meta
    ADD CONSTRAINT "$1" FOREIGN KEY (image_id) REFERENCES pjj_images(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $2; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_user_flags
    ADD CONSTRAINT "$2" FOREIGN KEY (chat_id) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: $2; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_image_meta
    ADD CONSTRAINT "$2" FOREIGN KEY (chat_id) REFERENCES pjj_chats(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: $3; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_image_meta
    ADD CONSTRAINT "$3" FOREIGN KEY (user_id) REFERENCES pjj_users(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: $4; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_image_meta
    ADD CONSTRAINT "$4" FOREIGN KEY (faction_id) REFERENCES pjj_factions(id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: auth; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_messages
    ADD CONSTRAINT auth FOREIGN KEY (author) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: auth; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_ban
    ADD CONSTRAINT auth FOREIGN KEY (auth) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: category; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_chats
    ADD CONSTRAINT category FOREIGN KEY (category) REFERENCES pjj_categories(id) ON UPDATE CASCADE ON DELETE SET DEFAULT;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_users
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_online
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_channels
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_factions
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_log
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_log_big
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userassoc
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userlist
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userbook
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userrecent
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_icons
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_themes
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_chatsettings
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_application_forms
    ADD CONSTRAINT chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chatchat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_ban
    ADD CONSTRAINT chatchat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: chatstates_chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userstates
    ADD CONSTRAINT chatstates_chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: creator; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_themes
    ADD CONSTRAINT creator FOREIGN KEY (creator) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: creator; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_application_forms
    ADD CONSTRAINT creator FOREIGN KEY (creator) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: faction; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_icons
    ADD CONSTRAINT faction FOREIGN KEY (faction) REFERENCES pjj_factions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: friend; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userfriends
    ADD CONSTRAINT friend FOREIGN KEY (friend) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: iban_chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_imageban
    ADD CONSTRAINT iban_chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: iban_user; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_imageban
    ADD CONSTRAINT iban_user FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: id; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_factionassoc
    ADD CONSTRAINT id FOREIGN KEY (id) REFERENCES pjj_factions(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: ips_country; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_country_ips
    ADD CONSTRAINT ips_country FOREIGN KEY (country) REFERENCES pjj_country_names(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: log_channel; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_log
    ADD CONSTRAINT log_channel FOREIGN KEY (channel) REFERENCES pjj_channels(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: parent; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_users
    ADD CONSTRAINT parent FOREIGN KEY (parent) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: parent; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_chats
    ADD CONSTRAINT parent FOREIGN KEY (parent) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: rcpt; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_messages
    ADD CONSTRAINT rcpt FOREIGN KEY (recipient) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: replace_chatid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_replaces
    ADD CONSTRAINT replace_chatid FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: rrcpt; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_messages
    ADD CONSTRAINT rrcpt FOREIGN KEY (realrecp) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: symbols_chat; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_symbols
    ADD CONSTRAINT symbols_chat FOREIGN KEY (chat) REFERENCES pjj_chats(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: theme; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_chats
    ADD CONSTRAINT theme FOREIGN KEY (theme) REFERENCES pjj_themes(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: uid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_ban
    ADD CONSTRAINT uid FOREIGN KEY (uid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: uid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_log
    ADD CONSTRAINT uid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: uid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userassoc
    ADD CONSTRAINT uid FOREIGN KEY (id) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: uid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userlist
    ADD CONSTRAINT uid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: uid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_online
    ADD CONSTRAINT uid FOREIGN KEY (uid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: userid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_factionassoc
    ADD CONSTRAINT userid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: userid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userbook
    ADD CONSTRAINT userid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: userid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userfriends
    ADD CONSTRAINT userid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: userid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_userrecent
    ADD CONSTRAINT userid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: userid; Type: FK CONSTRAINT; Schema: chatv3; Owner: -
--

ALTER TABLE ONLY pjj_icons
    ADD CONSTRAINT userid FOREIGN KEY (userid) REFERENCES pjj_users(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

