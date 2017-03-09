import json

def map(arr, map):
	json = {}

	for i, v in enumerate(arr):
		if i < len(map):
			json[map[i]] = v

	return json

def parseJSON(arr, _map):
	json_arr = []

	for r in arr:
		json_arr.append(map(r, _map))

	return json_arr

mapPersonas = ['id_persona', 'cedula', 'nombres', 'primer_apellido', 'segundo_apellido', 'email', 'estado', 'usuario', 'rol_integral', 'puesto_organizativo', 'unidad', 'proceso', 'empresa', 'sede', 'coach_cedula', 'coach_nombres', 'coach_primer_apellido', 'coach_segundo_apellido']

mapping = ['nombre', 'apellido', 'cedula', 'edad']
test = [('Eduardo', 'Lorenzo', '21115476', 23), ('Cristina', 'Lozano', '27176391', 19)]

print(parseJSON(test, mapping))