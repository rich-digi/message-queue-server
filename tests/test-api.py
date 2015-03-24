# --------------------------------------------------------------------------------
# Test MQS API
# --------------------------------------------------------------------------------

import glob, csv, string, re
import requests
import simplejson as json
from sys import argv, stdout

API_URL = 'mqs.loc'

class trml:
	BLACK 	= '\033[30m'
	RED 	= '\033[31m'
	GREEN 	= '\033[32m'
	BOLD	= '\033[1m'
	NORMAL	= '\033[0;0m'
    
# --------------------------------------------------------------------------------
# Make API call

def make_api_call(method, uri, payload):
	#
	# Make a Call to the REST API at uri, using HTTP method, with payload
	#
	method = method.upper()
	url = 'http://' + API_URL + uri
	res	= {'status': 0, 'headers': '', 'payload': '', 'time': 0}
	
	try:
	
		if method == 'GET':
			r = requests.get(url, params=payload)
		elif method == 'POST':
			r = requests.post(url, data=payload)
		elif method == 'PUT':
			r = requests.put(url, data=payload)
		elif method == 'PATCH':
			r = requests.patch(url, data=payload)
		elif method == 'DELETE':
			r = requests.delete(url)
		
		time_ms = int(round(r.elapsed.microseconds / float(1000), 0)); # Time in milliseconds
		res	= {
			'status': 				r.status_code,
			'headers':				r.headers,
			'payload': 				r.text,
			'time':					time_ms
			}
	
	except requests.ConnectionError:
		print trml.RED + 'CONNECTION FAILED' + trml.BLACK
	except requests.HTTPError:
		print trml.RED + 'INVALID HTTP RESPONSE' + trml.BLACK
	except requests.Timeout:
		print trml.RED + 'REQUEST TIMEOUT' + trml.BLACK
	except requests.exceptions.RequestException:
		print trml.RED + 'REQUEST EXCEPTION OF UNKNOWN TYPE' + trml.BLACK

	return res


# --------------------------------------------------------------------------------
# Normalize JSON values (compact, sort object keys in alphabetical order)

def normalize_json(str):
	str = json.dumps(json.loads(str), separators=(',', ':'), sort_keys=True)
	return str


# --------------------------------------------------------------------------------
# Test whether two strings decode to equivalent JSON values

def test_json_equivalence(a, b):
	a = normalize_json(a)
	b = normalize_json(b)
	return a == b


# --------------------------------------------------------------------------------
# process_data function

def process_data(input_pattern, column_positions, output_prefix, joiner_function):
	
	cp = column_positions	
	total = 0
	for file in glob.glob(input_pattern):
		print 'Reading tests from', file
		print
		fileno = re.match(r'.*(\d+).csv', file)
		output = open(output_prefix + fileno.group(1) + '.csv', 'wb')
		writer = csv.writer(output, delimiter=',', quotechar='"', quoting=csv.QUOTE_ALL)
		inpcsv = open(file, 'rU')
		reader = csv.reader(inpcsv, delimiter=',')
		rownum = 0
		for row in reader:
			extract_cols = range(len(row))
			content = list(row[i] for i in extract_cols)
			if rownum == 0:
				# Write header row
				writer.writerow(joiner_function(content))
			else:
				# Extract data from row
				uri 	= content[cp['uri']]
				method	= content[cp['method']]
				payload = content[cp['payload']]

				# Make API call
				res = make_api_call(method, uri, payload)
				print '{:7s} {:40s} {:5d}    {:10d}ms'.format(method, uri, res['status'], res['time'])
				
				# Record the result
				resline = content
				resline[cp['expected_payload']] = resline[cp['expected_payload']].strip()
				resline.append(res['status'])
				resline.append(res['payload'].strip())
				resline.append(res['time'])
				writer.writerow(joiner_function(resline))
				total += 1
			rownum += 1
		inpcsv.close()
		output.close()
	return total

# --------------------------------------------------------------------------------
# Check test results

def check_test_results(column_positions, output_prefix):
	stdout.write(trml.BOLD)
	seperator = '------------------------------------------------------------------------------------------------------'
	print
	print 'RESULTS'
	print seperator
	print '{:4s}      {:40s}      {:10s}      {:5s}      {:10s}    {:5s}'.format(
			'Line', 'URI', 'Expected', 'Got', 'Test', 'Time'
	)
	print seperator
	stdout.write(trml.NORMAL)
	cp = column_positions	
	passmsg = trml.GREEN + 'PASSED' + trml.BLACK
	failmsg = trml.RED   + 'FAILED' + trml.BLACK
	pcount = 0
	fcount = 0
	for file in glob.glob(output_prefix + '*.csv'):
		outcsv = open(file, 'rU')
		reader = csv.reader(outcsv, delimiter=',')
		rownum = 0
		for row in reader:
			extract_cols = range(len(row))
			content = list(row[i] for i in extract_cols)
			if rownum == 0:
				# Write header row
				extract_cols = range(len(row))
			else:
				# Extract data from row
				content = list(row[i] for i in extract_cols)
				uri  		= content[cp['uri']]
				exp_status  = content[cp['expected_status']]
				res_status  = content[cp['result_status']]
				exp_payload = content[cp['expected_payload']]
				res_payload = content[cp['result_payload']]
				time_taken  = content[cp['time_taken']]
				
				passed = exp_status == res_status and payload_meets_test_criteria(exp_payload, res_payload)
				if not passed:
					 stdout.write(trml.BOLD)
				print '{:4d}      {:40s}      {:10s}      {:5s}      {:10s}      {:>5s}ms'.format(
						rownum + 1, uri, exp_status, res_status, passmsg if passed else failmsg, time_taken
				)
				if not passed:
					 stdout.write(trml.NORMAL)
				if passed:
					pcount += 1
				else:
					fcount += 1
			rownum += 1
		outcsv.close()
	format = 'TEST RESULTS: ' + trml.GREEN + '%d PASSED ' + trml.RED + '%d FAILED'
	stdout.write(trml.BOLD)
	print seperator
	print format % (pcount, fcount), trml.BLACK
	print seperator
	print
	stdout.write(trml.NORMAL)


def payload_meets_test_criteria(exp, got):
	# Exp (expected) is a regex
	norm = normalize_json(got)
	"""
	print got
	print norm
	print exp
	"""
	return re.match(exp, norm)
		
	
# --------------------------------------------------------------------------------
# RUN

if __name__ == '__main__':
	print
	stdout.write(trml.BOLD)
	print '----------------'
	print 'TESTING REST API'
	print '----------------'
	stdout.write(trml.NORMAL)
	print
	in_pat  = 'data/api_input*.csv'
	col_pos = {
			'method': 			0,
			'uri': 				1,
			'payload': 			2,
			'expected_status': 	3,
			'expected_payload': 4,
			'result_status': 	5,
			'result_payload': 	6,
			'time_taken': 		7
			}
	out_pre = 'data/api_output'
	def joinfun(d) : return d 
	total = process_data(in_pat, col_pos, out_pre, joinfun)
	print
	print total, 'lines processed'
	print 'DONE'
	print
	# Check the results of the test
	check_test_results(col_pos, out_pre)
