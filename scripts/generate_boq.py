import xlsxwriter
import sys
import os
import json

def create_boq(project_name, filename, boq_data=None):
    workbook = xlsxwriter.Workbook(filename)
    
    # Formats
    title_fmt = workbook.add_format({'bold': True, 'size': 14, 'align': 'center', 'valign': 'vcenter', 'border': 1})
    header_fmt = workbook.add_format({'bold': True, 'bg_color': '#D7E4BC', 'border': 1, 'align': 'center'})
    cell_fmt = workbook.add_format({'border': 1})
    num_fmt = workbook.add_format({'border': 1, 'num_format': '#,##0.00'})
    subtotal_fmt = workbook.add_format({'bold': True, 'border': 1, 'bg_color': '#EEECE1', 'num_format': '#,##0.00'})
    grand_total_fmt = workbook.add_format({'bold': True, 'border': 1, 'bg_color': '#D9D9D9', 'num_format': '#,##0.00', 'size': 12})
    
    # Defaults if no JSON or old format
    if not boq_data or 'categories' not in boq_data:
        # Fallback to legacy or default structure
        sub_items = boq_data.get('sub', [
            {'no': '1.1', 'desc': 'Excavation & Earth Work', 'unit' : 'm3', 'qty': 0, 'rate': 0},
            {'no': '1.2', 'desc': 'Masonry Work', 'unit' : 'm3', 'qty': 0, 'rate': 0}
        ]) if boq_data else []
        super_items = boq_data.get('super', [
            {'no': '2.1', 'desc': 'Concrete Work', 'unit' : 'm3', 'qty': 0, 'rate': 0}
        ]) if boq_data else []
        
        boq_data = {
            'categories': [
                {'id': 'sub', 'title': 'SUB STRUCTURE', 'items': sub_items},
                {'id': 'super', 'title': 'SUPER STRUCTURE', 'items': super_items}
            ]
        }

    # --- SHEET 3: DETAILED BOQ ---
    ws3 = workbook.add_worksheet('Detailed BOQ')
    ws3.set_column('B:B', 60)
    ws3.set_column('C:F', 15)
    ws3.merge_range('A1:F1', f'DETAILED BILL OF QUANTITIES FOR {project_name.upper()}', title_fmt)
    
    headers = ['Item No', 'Description', 'Unit', 'Quantity', 'Rate', 'Amount']
    for col, head in enumerate(headers):
        ws3.write(2, col, head, header_fmt)

    row = 3
    summary_refs = []

    chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"
    for idx, cat in enumerate(boq_data['categories']):
        code = chars[idx] if idx < len(chars) else str(idx)
        ws3.write(row, 0, code, workbook.add_format({'bold': True}))
        ws3.write(row, 1, cat['title'].upper(), workbook.add_format({'bold': True}))
        row += 1
        
        start_row = row + 1
        for item in cat.get('items', []):
            qty = str(item.get('qty', 0))
            rate = str(item.get('rate', 0))
            
            ws3.write(row, 0, item.get('no', ''), cell_fmt)
            ws3.write(row, 1, item.get('desc', ''), cell_fmt)
            ws3.write(row, 2, item.get('unit', ''), cell_fmt)
            
            # Write Quantity (Formula or Value)
            if qty.startswith('='):
                ws3.write_formula(row, 3, qty, cell_fmt)
            else:
                try:
                    ws3.write(row, 3, float(qty), cell_fmt)
                except:
                    ws3.write(row, 3, qty, cell_fmt)

            # Write Rate (Formula or Value)
            if rate.startswith('='):
                ws3.write_formula(row, 4, rate, num_fmt)
            else:
                try:
                    ws3.write(row, 4, float(rate), num_fmt)
                except:
                    ws3.write(row, 4, rate, num_fmt)

            # Write Amount Formula
            ws3.write_formula(row, 5, f'=D{row+1}*E{row+1}', num_fmt)
            row += 1
            
        end_row = row
        ws3.write(row, 1, f"TOTAL CARRIED TO SUMMARY ({cat['title'].upper()})", subtotal_fmt)
        ws3.write_formula(row, 5, f'=SUM(F{start_row}:F{end_row})', subtotal_fmt)
        
        summary_refs.append({
            'code': code,
            'title': cat['title'],
            'ref': f"'Detailed BOQ'!F{row+1}"
        })
        row += 2

    # --- SHEET 2: BOQ SUMMARY ---
    ws2 = workbook.add_worksheet('BOQ Summary')
    ws2.set_column('B:B', 50); ws2.set_column('C:F', 15)
    ws2.merge_range('A1:F1', f'SUMMARY OF BILL OF QUANTITIES FOR {project_name.upper()}', title_fmt)
    
    summ_headers = ['Item No', 'Description', '', '', '', 'Amount (Birr)']
    for col, head in enumerate(summ_headers):
        if head: ws2.write(2, col, head, header_fmt)

    s_row = 3
    for s_ref in summary_refs:
        ws2.write(s_row, 0, s_ref['code'], cell_fmt)
        ws2.write(s_row, 1, s_ref['title'].upper(), cell_fmt)
        ws2.write_formula(s_row, 5, s_ref['ref'], num_fmt)
        s_row += 1
    
    subtotal_row = s_row + 1
    ws2.write(subtotal_row, 4, 'Total without VAT', workbook.add_format({'bold': True}))
    ws2.write_formula(subtotal_row, 5, f'=SUM(F4:F{s_row})', subtotal_fmt)
    
    vat_row = subtotal_row + 1
    ws2.write(vat_row, 4, 'VAT (15%)', workbook.add_format({'bold': True}))
    ws2.write_formula(vat_row, 5, f'=F{subtotal_row+1}*0.15', subtotal_fmt)
    
    grand_row = vat_row + 1
    ws2.write(grand_row, 4, 'TOTAL WITH VAT', workbook.add_format({'bold': True, 'bg_color': '#FFFF00'}))
    ws2.write_formula(grand_row, 5, f'=F{subtotal_row+1}+F{vat_row+1}', grand_total_fmt)
    
    # --- SHEET 1: GRAND SUMMARY ---
    ws1 = workbook.add_worksheet('Grand Summary')
    ws1.set_column('B:B', 60); ws1.set_column('D:D', 20)
    ws1.merge_range('A1:D1', f'GRAND SUMMARY FOR {project_name.upper()} PROJECT', title_fmt)
    
    for col, head in enumerate(['Item No', 'Description', 'Unit', 'Amount (Birr)']): 
        ws1.write(2, col, head, header_fmt)
        
    ws1.write(3, 0, '1', cell_fmt)
    ws1.write(3, 1, f'{project_name} (Build Project)', cell_fmt)
    ws1.write(3, 2, 'LS', cell_fmt)
    ws1.write_formula(3, 3, f"'BOQ Summary'!F{subtotal_row+1}", num_fmt)
    
    ws1.write(5, 1, 'Total without VAT', workbook.add_format({'bold': True}))
    ws1.write_formula(5, 3, f"'BOQ Summary'!F{subtotal_row+1}", num_fmt)
    
    ws1.write(6, 1, 'VAT (15%)', workbook.add_format({'bold': True}))
    ws1.write_formula(6, 3, f"'BOQ Summary'!F{vat_row+1}", num_fmt)
    
    ws1.write(7, 1, 'TOTAL WITH VAT (15%)', workbook.add_format({'bold': True, 'bg_color': '#D9E1F2'}))
    ws1.write_formula(7, 3, f"'BOQ Summary'!F{grand_row+1}", grand_total_fmt)

    workbook.close()

if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python generate_boq.py <project_name> <output_file> [json_data]")
        sys.exit(1)
    
    proj_name = sys.argv[1]
    out_file = sys.argv[2]
    data = None
    if len(sys.argv) > 3:
        try:
            data = json.loads(sys.argv[3])
        except:
            pass
            
    create_boq(proj_name, out_file, data)
    print(f"Created: {out_file}")
