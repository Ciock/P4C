--
-- PostgreSQL database dump
--

-- Dumped from database version 10.2
-- Dumped by pg_dump version 10.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: p4c; Type: SCHEMA; Schema: -; Owner: postgres
--

CREATE SCHEMA p4c;


ALTER SCHEMA p4c OWNER TO postgres;

SET search_path = p4c, pg_catalog;

--
-- Name: check_double_response(); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION check_double_response() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  counter   int;
  this_task int;

BEGIN
  select response.task
  into this_task
  from p4c.response
    join p4c.made_response on response.id = made_response.response
  where new.response = made_response.response;

  select count(responses) into counter
  from (
         select response
         from p4c.made_response
         where new.worker = worker and response in(
           select id
           from p4c.response
           where response.task = this_task)) as responses;
  if (counter <> 0)
  then
    raise exception 'Already answered this task';
    return null;
  end if;
  RETURN new;

END;
$$;


ALTER FUNCTION p4c.check_double_response() OWNER TO postgres;

--
-- Name: check_result(); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION check_result() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  w_counter              int;
  this_n_worker          int;
  count_max_response     int;
  this_majority_treshold int;
  my_treshold            float;
  this_task              int;
  correct_response       int;

BEGIN
  select
    task.id,
    task.n_worker,
    task.majority_threshold
  into this_task, this_n_worker, this_majority_treshold
  from p4c.task
    join p4c.response on task.id = response.task
    join p4c.made_response on response.id = made_response.response
  where new.response = made_response.response
  group by task.id;

  select count(*)
  into w_counter
  from p4c.task
    join p4c.response on task.id = response.task
    join p4c.made_response on response.id = made_response.response
  where this_task = task.id;

  if (w_counter >= this_n_worker)
  then
    select
      max(c2.counter),
      c2.responses
    into count_max_response, correct_response
    from (
           select
             count(worker)          as counter,
             made_response.response as responses
           from p4c.task
             join p4c.response on task.id = response.task
             join p4c.made_response on response.id = made_response.response
           group by made_response.response) as c2
    group by c2.responses;



    my_treshold = count_max_response :: float / w_counter;

    if (my_treshold > this_majority_treshold)
    then
      update p4c.task
      set result = true, response = correct_response
      where id = this_task;
    end if;

  end if;
  RETURN NULL;

END;
$$;


ALTER FUNCTION p4c.check_result() OWNER TO postgres;

--
-- Name: final_report(character varying, character varying); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION final_report(campagna character varying, requester_campaign character varying) RETURNS TABLE(total_tasks integer, running_task integer, validation_rate double precision)
    LANGUAGE plpgsql
    AS $$
BEGIN
  RETURN QUERY
  SELECT
    count(*)                                                                         as total,
    COUNT(*) - COUNT(T.result)                                                       as running,
    (cast(COUNT(*) as double precision) -
     cast(COUNT(T.result) as double precision) / cast(count(*) as double precision)) as rate
  FROM p4c.campaign AS C
    JOIN p4c.task AS T ON T.campaign = C.title
  WHERE C.title = campagna AND C.requester = requester_campaign
  GROUP BY C.title;

END;

$$;


ALTER FUNCTION p4c.final_report(campagna character varying, requester_campaign character varying) OWNER TO postgres;

--
-- Name: give_points(); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION give_points() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
DECLARE
  workers         varchar [];
  correct_workers varchar [];
  i               varchar(20);
  task_keywords   varchar [];
  j               varchar(60);

BEGIN

  workers = array(
      select worker
      from (
             -- Tutti i lavoratori al task
             select worker
             from p4c.made_response
             where made_response.response in (
               -- Risposte relative al task
               select response.id
               from p4c.response
                 join p4c.task on response.task = task.id
               where new.id = task.id)) as w1
      except (
        -- lavoratori che hanno risposto correttemente
        select worker
        from p4c.made_response
        where made_response.response = new.response)
  );

  correct_workers = array(
      select worker
      from p4c.made_response
      where made_response.response = new.response);

  task_keywords = array(
      select keyword
      from p4c.contains_keyword
      where contains_keyword.task = new.id);

  -- decremento score di lavoratori che hanno risposto sbagliato
  FOREACH i IN array workers LOOP
    UPDATE p4c.worker
    SET score = score - 1
    WHERE username = i;

    FOREACH j IN array task_keywords LOOP
      UPDATE p4c.got_skills
      SET skill_score = skill_score - 1
      WHERE got_skills.worker = i AND got_skills.skill similar to '%' || j || '%';
    END LOOP;
  END LOOP;

  IF (new.result IS TRUE)
  THEN
    -- incremento score di lavoratori che hanno risposto correttemente
    FOREACH i IN array correct_workers LOOP
      UPDATE p4c.worker
      SET score = score + 1
      WHERE username = i;

      FOREACH j IN array task_keywords LOOP
        UPDATE p4c.got_skills
        SET skill_score = skill_score + 1
        WHERE got_skills.worker = i AND skill like '%' || j || '%';
      END LOOP;

    END LOOP;

  ELSE
    -- decremento score di tutti lavoratori
    FOREACH i IN array correct_workers LOOP
      UPDATE p4c.worker
      SET score = score - 1
      WHERE username = i;

      FOREACH j IN array task_keywords LOOP
        UPDATE p4c.got_skills
        SET skill_score = skill_score - 1
        WHERE got_skills.worker = i AND skill like '%' || j || '%';
      END LOOP;

    END LOOP;

  END IF;

  RETURN NULL;
END;
$$;


ALTER FUNCTION p4c.give_points() OWNER TO postgres;

--
-- Name: report(character varying, character varying); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION report(campagna character varying, requester_campaign character varying) RETURNS TABLE(task_id integer, task character varying, task_result boolean)
    LANGUAGE plpgsql
    AS $$
DECLARE

BEGIN

  RETURN QUERY
  SELECT
    id,
    titolo,
    result
  FROM p4c.campaign AS C
    JOIN p4c.task AS T ON T.campaign = C.title
  WHERE C.title = campagna AND C.requester = requester_campaign;

END;
$$;


ALTER FUNCTION p4c.report(campagna character varying, requester_campaign character varying) OWNER TO postgres;

--
-- Name: stats(character varying); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION stats(this_worker character varying) RETURNS TABLE(lavoratore character varying, punteggio integer, posizione integer, task_eseguiti integer[], task_validi integer[])
    LANGUAGE plpgsql
    AS $$
DECLARE
  index      integer;
  workers    varchar [];
  valid_task varchar [];
  j          varchar(20);
BEGIN

  lavoratore = this_worker;

  select score
  into punteggio
  from p4c.worker
  where username = this_worker;

  workers = array(SELECT username
                  FROM p4c.worker
                  ORDER BY score DESC);

  posizione = 1;
  FOREACH j IN ARRAY workers LOOP
    IF this_worker = j
    THEN
      EXIT; -- l'index sarà la posizione in classifica
    ELSE
      posizione = posizione + 1;
    END IF;
  END LOOP;

  task_eseguiti = array(SELECT DISTINCT R.task
                FROM p4c.response AS R
                  JOIN p4c.made_response AS M
                    ON R.id = M.response
                WHERE M.worker = this_worker);

  task_validi = array(SELECT id
                     FROM p4c.task
                     WHERE task.result IS NOT NULL AND id = ANY (task_eseguiti));

  RETURN NEXT;

END;
$$;


ALTER FUNCTION p4c.stats(this_worker character varying) OWNER TO postgres;

--
-- Name: task_assignment(character varying); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION task_assignment(username character varying) RETURNS integer[]
    LANGUAGE plpgsql
    AS $$
DECLARE
  skills      varchar [];
  temp_task   integer [];
  tasks       integer [];
  j           integer;
  i           varchar(60);
  unique_task integer [];
BEGIN

  -- Restituisce l'id del task assegnato
  skills = array(SELECT got_skills.skill
                 FROM p4c.got_skills
                 WHERE worker = username);

  -- Skill del lavoratore
  FOREACH i IN ARRAY skills LOOP
    temp_task = array(SELECT task
                      FROM p4c.contains_keyword
                      WHERE i SIMILAR TO '%' || keyword || '%');

    FOREACH j IN array temp_task LOOP
      unique_task = array [j];
      IF (tasks @> unique_task)
      THEN
      ELSE 
        tasks = tasks || unique_task;
      END IF;
    END LOOP;

  END LOOP;

  -- Restituisco i task che gli propongo
  RETURN tasks;

END;

$$;


ALTER FUNCTION p4c.task_assignment(username character varying) OWNER TO postgres;

--
-- Name: top10(character varying, character varying); Type: FUNCTION; Schema: p4c; Owner: postgres
--

CREATE FUNCTION top10(campagna character varying, requester_campaign character varying) RETURNS TABLE(lavoratore character varying, punteggio integer)
    LANGUAGE plpgsql
    AS $$
DECLARE
  keywords     varchar [];
  users        varchar [];
  temp_users   varchar [];
  j            varchar(60);
  u            varchar(20);
  index        integer;
  unique_users varchar [];

BEGIN
  index = 1;
  FOR j in (SELECT DISTINCT keyword
            FROM p4c.contains_keyword
              JOIN p4c.task ON contains_keyword.task = task.id
              JOIN p4c.campaign ON task.campaign = campagna and task.requester = requester_campaign) LOOP
    keywords [index] = j;
    index = index + 1;
  end loop;

  FOREACH j IN ARRAY keywords LOOP
    temp_users = array(SELECT worker
                       FROM p4c.got_skills
                       WHERE skill SIMILAR TO '%' || j || '%');
    FOREACH u IN array temp_users LOOP
      unique_users = array [u];
      IF (users @> unique_users)
      THEN
      ELSE
        users = users || unique_users;
      END IF;
    end loop;
  END LOOP;

  -- Ora ordinamento e selezione dei primi 10
  RETURN QUERY
  SELECT
    username,
    score
  FROM p4c.worker
  WHERE username = any (users) -- tra un qualsiasi user in users
  ORDER BY score DESC
  LIMIT 10;

END;
$$;


ALTER FUNCTION p4c.top10(campagna character varying, requester_campaign character varying) OWNER TO postgres;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: campaign; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE campaign (
    title character varying(60) NOT NULL,
    requester character varying(20) NOT NULL,
    opening_date date NOT NULL,
    deadline_date date NOT NULL,
    registration_deadline_date date NOT NULL,
    CONSTRAINT campaign_check CHECK ((opening_date < deadline_date)),
    CONSTRAINT campaign_check1 CHECK ((opening_date <= registration_deadline_date))
);


ALTER TABLE campaign OWNER TO postgres;

--
-- Name: contains_keyword; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE contains_keyword (
    task integer NOT NULL,
    keyword character varying(60) NOT NULL
);


ALTER TABLE contains_keyword OWNER TO postgres;

--
-- Name: got_skills; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE got_skills (
    worker character varying(20) NOT NULL,
    skill character varying(60) NOT NULL,
    skill_score integer DEFAULT 10
);


ALTER TABLE got_skills OWNER TO postgres;

--
-- Name: keyword; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE keyword (
    keyword character varying(60) NOT NULL
);


ALTER TABLE keyword OWNER TO postgres;

--
-- Name: made_response; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE made_response (
    worker character varying(20) NOT NULL,
    response integer NOT NULL
);


ALTER TABLE made_response OWNER TO postgres;

--
-- Name: requester; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE requester (
    username character varying(20) NOT NULL
);


ALTER TABLE requester OWNER TO postgres;

--
-- Name: response; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE response (
    id integer NOT NULL,
    name character varying(45) NOT NULL,
    task integer NOT NULL
);


ALTER TABLE response OWNER TO postgres;

--
-- Name: response_id_seq; Type: SEQUENCE; Schema: p4c; Owner: postgres
--

CREATE SEQUENCE response_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE response_id_seq OWNER TO postgres;

--
-- Name: response_id_seq; Type: SEQUENCE OWNED BY; Schema: p4c; Owner: postgres
--

ALTER SEQUENCE response_id_seq OWNED BY response.id;


--
-- Name: skills; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE skills (
    name character varying(60) NOT NULL,
    knowledge boolean NOT NULL,
    attitude boolean NOT NULL,
    CONSTRAINT skills_check CHECK ((knowledge <> attitude))
);


ALTER TABLE skills OWNER TO postgres;

--
-- Name: task; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE task (
    id integer NOT NULL,
    titolo character varying(60) NOT NULL,
    description text,
    n_worker integer NOT NULL,
    majority_threshold double precision DEFAULT (70.0 / (100)::numeric),
    result boolean,
    requester character varying(20) NOT NULL,
    campaign character varying(60) NOT NULL,
    response integer
);


ALTER TABLE task OWNER TO postgres;

--
-- Name: task_id_seq; Type: SEQUENCE; Schema: p4c; Owner: postgres
--

CREATE SEQUENCE task_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE task_id_seq OWNER TO postgres;

--
-- Name: task_id_seq; Type: SEQUENCE OWNED BY; Schema: p4c; Owner: postgres
--

ALTER SEQUENCE task_id_seq OWNED BY task.id;


--
-- Name: user; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE "user" (
    username character varying(20) NOT NULL,
    password text NOT NULL
);


ALTER TABLE "user" OWNER TO postgres;

--
-- Name: worker; Type: TABLE; Schema: p4c; Owner: postgres
--

CREATE TABLE worker (
    username character varying(20) NOT NULL,
    score integer DEFAULT 10 NOT NULL
);


ALTER TABLE worker OWNER TO postgres;

--
-- Name: response id; Type: DEFAULT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY response ALTER COLUMN id SET DEFAULT nextval('response_id_seq'::regclass);


--
-- Name: task id; Type: DEFAULT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY task ALTER COLUMN id SET DEFAULT nextval('task_id_seq'::regclass);


--
-- Data for Name: campaign; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY campaign (title, requester, opening_date, deadline_date, registration_deadline_date) FROM stdin;
sondaggione	culocane	2018-03-21	2018-03-22	2018-03-22
\.


--
-- Data for Name: contains_keyword; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY contains_keyword (task, keyword) FROM stdin;
2	politica
2	arte
2	cibo
\.


--
-- Data for Name: got_skills; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY got_skills (worker, skill, skill_score) FROM stdin;
worker1	esperto di musica	10
worker2	cultore di videogiochi	10
worker4	viaggiatore	10
worker5	studioso di medicina	10
worker5	esperto di chimica	10
worker2	esperto di elettronica	10
worker6	critico di cibo	10
worker3	modaiolo	10
worker3	esperto di politica	10
\.


--
-- Data for Name: keyword; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY keyword (keyword) FROM stdin;
musica
cinema
videogiochi
libri
arte
elettronica
informatica
letteratura
politica
cibo
medicina
programmazione
chimica
fisica
filosofia
economia
videomaking
social networks
moda
italiano
storia
calcio
viaggi
fotografia
crittovalute
\.


--
-- Data for Name: made_response; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY made_response (worker, response) FROM stdin;
worker1	1
worker3	1
worker4	1
worker2	1
worker5	2
\.


--
-- Data for Name: requester; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY requester (username) FROM stdin;
culocane
\.


--
-- Data for Name: response; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY response (id, name, task) FROM stdin;
1	pomodoro	2
2	forchetta	2
\.


--
-- Data for Name: skills; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY skills (name, knowledge, attitude) FROM stdin;
esperto di musica	t	f
esperto di cinema	t	f
cultore di videogiochi	f	t
divoratore di libri	f	t
esperto d'arte	t	f
esperto di elettronica	t	f
esperto di informatica	t	f
amante della letteratura	t	f
esperto di politica	f	t
critico di cibo	f	t
studioso di medicina	t	f
esperto di programmazione	t	f
esperto di chimica	t	f
esperto di fisica	t	f
amante della filosofia	t	f
esperto di economia	t	f
studente di videomaking	f	t
studioso di social networks	f	t
modaiolo	f	t
madrelingua italiano	f	t
esperto di storia	t	f
telecronista di calcio	f	t
viaggiatore	f	t
amante della fotografia	f	t
miner di crittovalute	t	f
\.


--
-- Data for Name: task; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY task (id, titolo, description, n_worker, majority_threshold, result, requester, campaign, response) FROM stdin;
2	beppeGrillo	\N	4	0.5	t	culocane	sondaggione	1
\.


--
-- Data for Name: user; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY "user" (username, password) FROM stdin;
culocane	caneculo
worker1	pass
worker2	pass
worker3	pass
worker4	pass
worker5	pass
worker6	pass\r\n\r\n\r\n\r\n
\.


--
-- Data for Name: worker; Type: TABLE DATA; Schema: p4c; Owner: postgres
--

COPY worker (username, score) FROM stdin;
worker6	10
worker5	8
worker1	10
worker3	10
worker4	10
worker2	10
\.


--
-- Name: response_id_seq; Type: SEQUENCE SET; Schema: p4c; Owner: postgres
--

SELECT pg_catalog.setval('response_id_seq', 2, true);


--
-- Name: task_id_seq; Type: SEQUENCE SET; Schema: p4c; Owner: postgres
--

SELECT pg_catalog.setval('task_id_seq', 2, true);


--
-- Name: campaign campaign_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY campaign
    ADD CONSTRAINT campaign_pkey PRIMARY KEY (title, requester);


--
-- Name: contains_keyword contains_keyword_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY contains_keyword
    ADD CONSTRAINT contains_keyword_pkey PRIMARY KEY (task, keyword);


--
-- Name: got_skills got_skills_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY got_skills
    ADD CONSTRAINT got_skills_pkey PRIMARY KEY (worker, skill);


--
-- Name: keyword keyword_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY keyword
    ADD CONSTRAINT keyword_pkey PRIMARY KEY (keyword);


--
-- Name: made_response made_response_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY made_response
    ADD CONSTRAINT made_response_pkey PRIMARY KEY (worker, response);


--
-- Name: requester requester_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY requester
    ADD CONSTRAINT requester_pkey PRIMARY KEY (username);


--
-- Name: response response_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY response
    ADD CONSTRAINT response_pkey PRIMARY KEY (id);


--
-- Name: skills skills_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY skills
    ADD CONSTRAINT skills_pkey PRIMARY KEY (name);


--
-- Name: task task_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_pkey PRIMARY KEY (id);


--
-- Name: user user_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY "user"
    ADD CONSTRAINT user_pkey PRIMARY KEY (username);


--
-- Name: worker worker_pkey; Type: CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY worker
    ADD CONSTRAINT worker_pkey PRIMARY KEY (username);


--
-- Name: task addpoints; Type: TRIGGER; Schema: p4c; Owner: postgres
--

CREATE TRIGGER addpoints AFTER UPDATE ON task FOR EACH ROW EXECUTE PROCEDURE give_points();


--
-- Name: made_response onmoreresponseonsametask; Type: TRIGGER; Schema: p4c; Owner: postgres
--

CREATE TRIGGER onmoreresponseonsametask BEFORE INSERT ON made_response FOR EACH ROW EXECUTE PROCEDURE check_double_response();


--
-- Name: made_response onnumworkerreached; Type: TRIGGER; Schema: p4c; Owner: postgres
--

CREATE TRIGGER onnumworkerreached AFTER INSERT ON made_response FOR EACH ROW EXECUTE PROCEDURE check_result();


--
-- Name: campaign campaign_requester_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY campaign
    ADD CONSTRAINT campaign_requester_fkey FOREIGN KEY (requester) REFERENCES requester(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: contains_keyword contains_keyword_keyword_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY contains_keyword
    ADD CONSTRAINT contains_keyword_keyword_fkey FOREIGN KEY (keyword) REFERENCES keyword(keyword) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: contains_keyword contains_keyword_task_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY contains_keyword
    ADD CONSTRAINT contains_keyword_task_fkey FOREIGN KEY (task) REFERENCES task(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: got_skills got_skills_skill_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY got_skills
    ADD CONSTRAINT got_skills_skill_fkey FOREIGN KEY (skill) REFERENCES skills(name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: got_skills got_skills_worker_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY got_skills
    ADD CONSTRAINT got_skills_worker_fkey FOREIGN KEY (worker) REFERENCES worker(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: made_response made_response_response_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY made_response
    ADD CONSTRAINT made_response_response_fkey FOREIGN KEY (response) REFERENCES response(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: made_response made_response_worker_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY made_response
    ADD CONSTRAINT made_response_worker_fkey FOREIGN KEY (worker) REFERENCES worker(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: requester requester_username_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY requester
    ADD CONSTRAINT requester_username_fkey FOREIGN KEY (username) REFERENCES "user"(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: response response_task_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY response
    ADD CONSTRAINT response_task_fkey FOREIGN KEY (task) REFERENCES task(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: task task_campaign_title_requester_fk; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_campaign_title_requester_fk FOREIGN KEY (campaign, requester) REFERENCES campaign(title, requester) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: task task_response_id_fk; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY task
    ADD CONSTRAINT task_response_id_fk FOREIGN KEY (response) REFERENCES response(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: worker worker_username_fkey; Type: FK CONSTRAINT; Schema: p4c; Owner: postgres
--

ALTER TABLE ONLY worker
    ADD CONSTRAINT worker_username_fkey FOREIGN KEY (username) REFERENCES "user"(username) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

