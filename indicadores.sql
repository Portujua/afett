select
    rqc.DES_QUESTION as indicador,
    rwqc.QTA_SCORE as puntuacion,
    rwqc.DES_ANSWER as puntuacion_descripcion,
    tbqc.NME_QSTCATEGORY as competencia,
    tbqs.NME_QSTSUBJECT cluster_,
    rqc.NME_QUESTION me_question,
    rval.ID_VALUTPREST as id_valutprest,
    rval.PRG_RIGA as prg_riga,
    persona.CSF_CFSPERSONA as cedula
from 
    EVALROWANS era, 
    R_QSTCAT rqc, 
    RW_QSTCAT rwqc, 
    QSTBLOCK qb,
    TB_QSTCAT tbqc,
    TB_QSTSUBJ tbqs,
    R_VALRUOLO rval,
    ANAGPERS persona
where
    era.ID_R_QSTCAT=rqc.ID_R_QSTCAT and
    era.ID_RW_QSTCATALOG=rwqc.ID_RW_QSTCATALOG and
    rqc.ID_QSTBLOCK=qb.ID_QSTBLOCK and
    qb.ID_QSTCATEGORY=tbqc.ID_QSTCATEGORY and
    tbqs.ID_QSTSUBJECT=tbqc.ID_QSTSUBJECT and
    rval.ID_VALUTPREST=era.ID_VALUTPREST and
    rval.PRG_RIGA=era.PRG_RIGA and
    (rqc.NME_QUESTION='145' or rqc.NME_QUESTION='146') and
    persona.ID_PERSONA=rval.ID_PERSONA









select
    rqc.DES_QUESTION as indicador,
    rwqc.QTA_SCORE as puntuacion,
    rwqc.DES_ANSWER as puntuacion_descripcion,
    tbqc.NME_QSTCATEGORY as competencia,
    rqc.NME_QUESTION me_question
from 
    EVALROWANS era, 
    R_QSTCAT rqc, 
    RW_QSTCAT rwqc, 
    QSTBLOCK qb,
    TB_QSTCAT tbqc
where
    era.ID_R_QSTCAT=rqc.ID_R_QSTCAT and
    era.ID_RW_QSTCATALOG=rwqc.ID_RW_QSTCATALOG and
    rqc.ID_QSTBLOCK=qb.ID_QSTBLOCK and
    qb.ID_QSTCATEGORY=tbqc.ID_QSTCATEGORY