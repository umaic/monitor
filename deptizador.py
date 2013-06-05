import psycopg2 as pg, MySQLdb as my


sqlu = "UPDATE location SET state_id = (SELECT id FROM state WHERE \
        divipola='%s') WHERE id = %d;\n"

sqlp = "SELECT id_espacia FROM depto WHERE \
                      ST_Within(ST_PointFromText('POINT(%f %f)', 4326), the_geom) IS \
                      NOT FALSE"

ps = pg.connect(database="sissh_wsg84", user="sissh", password="mjuiokm")
pgcur = ps.cursor()


def dep(db):
    
    print '**********' + db 
    f = open('%s.sql' % db, 'w')
    
    m = my.connect(passwd="!7ujmmju7!",user="monitor",db=db)
    mycur = m.cursor()

    mycur.execute("SELECT longitude, latitude, i.id, l.id, l.location_name, \
                  l.state_id FROM location l \
                   JOIN incident i ON l.id = i.location_id WHERE state_id = 0")

    print "SELECT longitude, latitude, i.id, l.id, l.location_name, \
                  l.state_id FROM location l \
                   JOIN incident i ON l.id = i.location_id WHERE state_id = 0"

    for row in mycur.fetchall():
        print row[5]
        if row[5] is not None:
            
            _lon = row[0]
            _lat = row[1]
            _iid = row[2]
            _lid = row[3]
            _nom = row[4]

            if (abs(_lon) > 0 and abs(_lat) > 0):

                print 'EventoID=%d, LocationID=%d, Longitude=%f, Latitude=%f, \
                Nombre=%s' % (_iid, _lid, _lon, _lat, _nom)

                if row[2] not in [5331,5337]:
                    pgcur.execute(sqlp % (_lon, _lat))
                    _id = pgcur.fetchone()
                    sid = _id[0]
                    lid = _lid
                else:
                    sid = 88
                    lid = 5331

                f.write(sqlu % (sid, lid))

dep('inundaciones')
#dep('ecompleja') Ya no, se hace desde plugin de sync con sidih
