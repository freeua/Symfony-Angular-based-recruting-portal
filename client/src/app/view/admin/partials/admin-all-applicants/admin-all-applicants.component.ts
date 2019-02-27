import { Component, OnInit } from '@angular/core';
import { AdminInterviewList} from '../../../../../entities/models-admin';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';
import { Router } from '@angular/router';
import { Angular5Csv } from 'angular5-csv/Angular5-csv';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';

@Component({
  selector: 'app-admin-all-applicants',
  templateUrl: './admin-all-applicants.component.html',
  styleUrls: ['./admin-all-applicants.component.scss']
})
export class AdminAllApplicantsComponent implements OnInit {

  public allApplicants = Array<AdminInterviewList>();

  public preloaderPage = true;
  public totalCount: number;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;
  public modalActiveClose: any;
  public selectedBusinessId;
  public selectedBusinessJob;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _sharedService: SharedService,
    private readonly _router: Router,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.allApplicants = [];
    this.getAdminAllApplicants('');
  }

  /**
   * Router admin for candidate on id
   * @param id
   */
  public routeCandidate(id) {
    this._router.navigate(['/admin/edit_candidate'], { queryParams: { candidateId: id} });
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessId = id;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param jobId {number} - job id to be used for fetching data and showing in popup
   * @param clientID {number} - job id to be used for fetching data and showing in popup
   * @param subcontent {any} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCenterJob(content: any, jobId, clientID, subcontent): void {
    if (jobId){
      this.selectedBusinessJob = {
        id: jobId
      };
      this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    } else {
      this.selectedBusinessId = clientID;
      this.modalActiveClose = this._modalService.open(subcontent, { centered: true, 'size': 'lg' });
    }
  }

  /**
   * Export CSV file
   * @return {Promise<void>}
   */
  public async exportDataCSV(search): Promise<void>{
    try {
      const response = await this._adminService.getAdminAllApplicants(search, 1, true);

      const options = {
        showLabels: true,
        headers: ['Candidate', 'Company', 'Position wanted' , 'Status']
      };

      new Angular5Csv(response, 'All applicants', options);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.allApplicants = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public loadPagination(): void {
    this.pagination++;
    this.paginationLoader = true;
    this.getAdminAllApplicants('');
  }

  /**
   * Get all applicants for admin
   * @param search {string}
   * @return {Promise<void>}
   */
  public async getAdminAllApplicants(search): Promise<void> {
    try {
      const response = await this._adminService.getAdminAllApplicants(search, this.pagination, false);

      response.items.forEach((item) => {
        this.allApplicants.push(item);
      });

      if (response.pagination.total_count === this.allApplicants.length) {
        this.loadMoreCheck = false;
      }
      else if (response.pagination.total_count !== this.allApplicants.length){
        this.loadMoreCheck = true;
      }

      this.totalCount = response.pagination.total_count;
      this.preloaderPage = false;
      this.paginationLoader = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Select change router
   * @param url
   */
  public routerApplicants(url): void {
    this._router.navigate([url.selectedValues[0]]);
  }

}
